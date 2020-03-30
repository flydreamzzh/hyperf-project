<?php


namespace App\Core\Components;


namespace App\Core\Components;
use App\Core\Traits\HyStaticInstance;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Hyperf\HttpMessage\Cookie\Cookie;
use App\Constants\StatusCode;
use Hyperf\Utils\Context;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;
use Hyperf\Contract\StdoutLoggerInterface;

/**
 * 请求响应结果
 * @package App\Container
 */
class Response
{

    use HyStaticInstance;

    /**
     * @var StdoutLoggerInterface
     */
    protected $logger;

    /**
     * @Inject
     * @var RequestInterface
     */
    protected $request;

    /**
     * @Inject
     * @var ResponseInterface
     */
    protected $response;

    const FORMAT_RAW = 'raw';
    const FORMAT_HTML = 'html';
    const FORMAT_JSON = 'json';
    const FORMAT_JSONP = 'jsonp';
    const FORMAT_XML = 'xml';

    /**
     * success
     * 返回请求结果
     * @param int $code
     * @param array|string $data
     * @param string|null $msg
     * @param string $format
     * @return PsrResponseInterface
     */
    public function send($code = StatusCode::SUCCESS, $data = [], string $msg = null, $format = Response::FORMAT_JSON)
    {
        $msg = $msg ?? StatusCode::instance()->getMessage(StatusCode::SUCCESS);;
        $data = [
            'code' => $code,
            'msg'=> $msg,
            'data' => $data
        ];
        if (in_array($format, [Response::FORMAT_JSON, Response::FORMAT_XML, Response::FORMAT_RAW])) {
            $response = $this->response->{$format}($data);
        } else {
            $response = $this->response->json($data);
        }
        return $response;
    }

    /**
     * @param array $data
     * @return PsrResponseInterface
     */
    public function json(array $data)
    {
        return $this->response->json($data);
    }

    /**
     * @param array $data
     * @return PsrResponseInterface
     */
    public function xml(array $data)
    {
        return $this->response->xml($data);
    }

    /**
     * @param string $url
     * @param string $schema
     * @param int $status
     * @return PsrResponseInterface
     */
    public function redirect(string $url,string $schema = 'http', int $status = 302 )
    {
        return $this->response->redirect($url,$status,$schema);
    }

    /**
     * @param string $file
     * @param string $name
     * @return PsrResponseInterface
     */
    public function download(string $file, string $name = '')
    {
        return $this->response->redirect($file,$name);
    }

    /**
     * @param string $name
     * @param string $value
     * @param int $expire
     * @param string $path
     * @param string $domain
     * @param bool $secure
     * @param bool $httpOnly
     * @param bool $raw
     * @param string|null $sameSite
     */
    public function cookie(string $name,string $value = '', $expire = 0, string $path = '/', string $domain = '', bool $secure = false, bool $httpOnly = true, bool $raw = false, ?string $sameSite = null)
    {
        // convert expiration time to a Unix timestamp
        if ($expire instanceof \DateTimeInterface) {
            $expire = $expire->format('U');
        } elseif (! is_numeric($expire)) {
            $expire = strtotime($expire);
            if ($expire === false) {
                throw new \RuntimeException('The cookie expiration time is not valid.');
            }
        }

        $cookie = new Cookie($name, $value, $expire, $path, $domain, $secure, $httpOnly, $raw, $sameSite);
        $response = $this->response->withCookie($cookie);
        Context::set(PsrResponseInterface::class, $response);
        return;
    }
}