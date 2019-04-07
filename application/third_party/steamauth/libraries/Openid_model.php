<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Openid_model extends CI_Model
{

	private		$identity,
				$dbg;
	
	protected	$server,
				$setup_url = null,
				$trustRoot;








    public function __construct()
	{
		log_message('debug', '=== Begin construct of OpenID_Model');
		log_message('debug', '=== Construct of OpenID_Model :: Try to load config');
		get_instance()->config->load('steamconfig');
		log_message('debug', '=== Construct of OpenID_Model :: Config loaded');
		$dbg = get_instance()->config->item('domainname');
		log_message('debug', '=== domainname = '.$dbg);
		$this->set_realm(get_instance()->config->item('domainname'));
		
		$uri = rtrim(preg_replace('#((?<=\?)|&)openid\.[^&]+#', '', $_SERVER['REQUEST_URI']), '?');
		$this->returnUrl = $this->trustRoot . $uri;
		
		$this->data = ($_SERVER['REQUEST_METHOD'] === 'POST') ? $_POST : $_GET;
		
		if(!function_exists('curl_init') && !in_array('https', stream_get_wrappers()))
		{
			throw new ErrorException('You must have either https wrappers or curl enabled');
		}
		log_message('debug', '=== End of construct of OpenID_Model');
	}

    function __isset($name)
    {	
		/* Заменить на isset ? */
        return in_array($name, array('identity', 'trustRoot', 'realm', 'xrdsOverride', 'mode'));
    }
	
    function __get($name)
    {
        switch ($name) {
        case 'identity':
            # We return claimed_id instead of identity,
            # because the developer should see the claimed identifier,
            # i.e. what he set as identity, not the op-local identifier (which is what we verify)
            return $this->claimed_id;
        case 'trustRoot':
        case 'realm':
            return $this->trustRoot;
        case 'mode':
            return empty($this->data['openid_mode']) ? null : $this->data['openid_mode'];
        }
    }
    
	
	
	
	
    function authUrl($immediate = false)
    {
        if ($this->setup_url && !$immediate) return $this->setup_url;
        if (!$this->server) $this->discover($this->p_identity_id);

        if ($this->version == 2) {
            return $this->authUrl_v2($immediate);
        }
        return $this->authUrl_v1($immediate);
    }

    function discover($url)
    {
        if (!$url) throw new ErrorException('No p_identity_id supplied.');
        # Use xri.net proxy to resolve i-name identities
        if (!preg_match('#^https?:#', $url)) {
            $url = "https://xri.net/$url";
        }

        # We save the original url in case of Yadis discovery failure.
        # It can happen when we'll be lead to an XRDS document
        # which does not have any OpenID2 services.
        $originalUrl = $url;

        # A flag to disable yadis discovery in case of failure in headers.
        $yadis = true;
        
        # Allows optional regex replacement of the URL, e.g. to use Google Apps
        # as an OpenID provider without setting up XRDS on the domain hosting.
        if (!is_null($this->xrds_override_pattern) && !is_null($this->xrds_override_replacement)) {
            $url = preg_replace($this->xrds_override_pattern, $this->xrds_override_replacement, $url);
        }

        # We'll jump a maximum of 5 times, to avoid endless redirections.
        for ($i = 0; $i < 5; $i ++) {
            if ($yadis) {
                $headers = $this->request($url, 'HEAD', array(), true);

                $next = false;
                if (isset($headers['x-xrds-location'])) {
                    $url = $this->build_url(parse_url($url), parse_url(trim($headers['x-xrds-location'])));
                    $next = true;
                }

                if (isset($headers['content-type']) && $this->is_allowed_type($headers['content-type'])) {
                    # Found an XRDS document, now let's find the server, and optionally delegate.
                    $content = $this->request($url, 'GET');

                    preg_match_all('#<Service.*?>(.*?)</Service>#s', $content, $m);
                    foreach($m[1] as $content) {
                        $content = ' ' . $content; # The space is added, so that strpos doesn't return 0.

                        # OpenID 2
                        $ns = preg_quote('http://specs.openid.net/auth/2.0/', '#');
                        if(preg_match('#<Type>\s*'.$ns.'(server|signon)\s*</Type>#s', $content, $type)) {
                            if ($type[1] == 'server') $this->identifier_select = true;

                            preg_match('#<URI.*?>(.*)</URI>#', $content, $server);
                            preg_match('#<(Local|Canonical)ID>(.*)</\1ID>#', $content, $delegate);
                            if (empty($server)) {
                                return false;
                            }
                            # Does the server advertise support for either AX or SREG?
                            $this->ax   = (bool) strpos($content, '<Type>http://openid.net/srv/ax/1.0</Type>');
                            $this->sreg = strpos($content, '<Type>http://openid.net/sreg/1.0</Type>')
                                       || strpos($content, '<Type>http://openid.net/extensions/sreg/1.1</Type>');

                            $server = $server[1];
                            if (isset($delegate[2])) $this->p_identity_id = trim($delegate[2]);
                            $this->version = 2;

                            $this->server = $server;
                            return $server;
                        }

                        # OpenID 1.1
                        $ns = preg_quote('http://openid.net/signon/1.1', '#');
                        if (preg_match('#<Type>\s*'.$ns.'\s*</Type>#s', $content)) {

                            preg_match('#<URI.*?>(.*)</URI>#', $content, $server);
                            preg_match('#<.*?Delegate>(.*)</.*?Delegate>#', $content, $delegate);
                            if (empty($server)) {
                                return false;
                            }
                            # AX can be used only with OpenID 2.0, so checking only SREG
                            $this->sreg = strpos($content, '<Type>http://openid.net/sreg/1.0</Type>')
                                       || strpos($content, '<Type>http://openid.net/extensions/sreg/1.1</Type>');

                            $server = $server[1];
                            if (isset($delegate[1])) $this->p_identity_id = $delegate[1];
                            $this->version = 1;

                            $this->server = $server;
                            return $server;
                        }
                    }

                    $next = true;
                    $yadis = false;
                    $url = $originalUrl;
                    $content = null;
                    break;
                }
                if ($next) continue;

                # There are no relevant information in headers, so we search the body.
                $content = $this->request($url, 'GET', array(), true);

                if (isset($this->headers['x-xrds-location'])) {
                    $url = $this->build_url(parse_url($url), parse_url(trim($this->headers['x-xrds-location'])));
                    continue;
                }

                $location = $this->htmlTag($content, 'meta', 'http-equiv', 'X-XRDS-Location', 'content');
                if ($location) {
                    $url = $this->build_url(parse_url($url), parse_url($location));
                    continue;
                }
            }

            if (!$content) $content = $this->request($url, 'GET');

            # At this point, the YADIS Discovery has failed, so we'll switch
            # to openid2 HTML discovery, then fallback to openid 1.1 discovery.
            $server   = $this->htmlTag($content, 'link', 'rel', 'openid2.provider', 'href');
            $delegate = $this->htmlTag($content, 'link', 'rel', 'openid2.local_id', 'href');
            $this->version = 2;

            if (!$server) {
                # The same with openid 1.1
                $server   = $this->htmlTag($content, 'link', 'rel', 'openid.server', 'href');
                $delegate = $this->htmlTag($content, 'link', 'rel', 'openid.delegate', 'href');
                $this->version = 1;
            }

            if ($server) {
                # We found an OpenID2 OP Endpoint
                if ($delegate) {
                    # We have also found an OP-Local ID.
                    $this->p_identity_id = $delegate;
                }
                $this->server = $server;
                return $server;
            }

            throw new ErrorException("No OpenID Server found at $url", 404);
        }
        throw new ErrorException('Endless redirection!', 500);
    }

    protected function set_realm($uri)
    {
		log_message('debug', '=== set_realm($uri) = '.$uri);
        $realm = '';
        
        # Set a protocol, if not specified.
        $realm .= (($offset = strpos($uri, '://')) === false) ? $this->get_realm_protocol() : '';
        
        # Set the offset properly.
        $offset = (($offset !== false) ? $offset + 3 : 0);
        
        # Get only the root, without the path.
        $realm .= (($end = strpos($uri, '/', $offset)) === false) ? $uri : substr($uri, 0, $end);
        
        $this->trustRoot = $realm;
    }

    protected function get_realm_protocol()
    {
        if (!empty($_SERVER['HTTPS'])) {
            $use_secure_protocol = ($_SERVER['HTTPS'] != 'off');
        } else if (isset($_SERVER['HTTP_X_FORWARDED_PROTO'])) {
            $use_secure_protocol = ($_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https');
        } else if (isset($_SERVER['HTTP__WSSC'])) {
            $use_secure_protocol = ($_SERVER['HTTP__WSSC'] == 'https');
        } else {
                $use_secure_protocol = false;
        }
        
        return $use_secure_protocol ? 'https://' : 'http://';
    }

	
}

