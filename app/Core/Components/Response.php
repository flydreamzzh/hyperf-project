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
     * @var array list of HTTP status codes and the corresponding texts
     */
    public static $httpStatuses = [
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',
        118 => 'Connection timed out',
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-Status',
        208 => 'Already Reported',
        210 => 'Content Different',
        226 => 'IM Used',
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        306 => 'Reserved',
        307 => 'Temporary Redirect',
        308 => 'Permanent Redirect',
        310 => 'Too many Redirect',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Time-out',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested range unsatisfiable',
        417 => 'Expectation failed',
        418 => 'I\'m a teapot',
        421 => 'Misdirected Request',
        422 => 'Unprocessable entity',
        423 => 'Locked',
        424 => 'Method failure',
        425 => 'Unordered Collection',
        426 => 'Upgrade Required',
        428 => 'Precondition Required',
        429 => 'Too Many Requests',
        431 => 'Request Header Fields Too Large',
        449 => 'Retry With',
        450 => 'Blocked by Windows Parental Controls',
        451 => 'Unavailable For Legal Reasons',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway or Proxy Error',
        503 => 'Service Unavailable',
        504 => 'Gateway Time-out',
        505 => 'HTTP Version not supported',
        507 => 'Insufficient storage',
        508 => 'Loop Detected',
        509 => 'Bandwidth Limit Exceeded',
        510 => 'Not Extended',
        511 => 'Network Authentication Required',
    ];

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
        if (!preg_match("/^(\d)+$/", $code)) {
            $msg = $code;
            $code = StatusCode::ERR_OTHER_EXCEPTION;
        }
        $msg = $msg ?? StatusCode::instance()->getMessage($code);
        $data = [
            'code' => (int)$code,
            'msg' => $msg,
            'data' => $data
        ];
        if (in_array($format, [Response::FORMAT_JSON, Response::FORMAT_XML, Response::FORMAT_RAW])) {
            $response = $this->response->{$format}($data);
        } else {
            $response = $this->response->json($data);
        }
        return $response;
    }

    public function withStatus($code)
    {
        $this->response->withStatus($code);
        return $this;
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
    public function redirect(string $url, string $schema = 'http', int $status = 302)
    {
        return $this->response->redirect($url, $status, $schema);
    }

    /**
     * @param string $file
     * @param string $name
     * @return PsrResponseInterface
     */
    public function download(string $file, string $name = '')
    {
        return $this->response->redirect($file, $name);
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
    public function cookie(string $name, string $value = '', $expire = 0, string $path = '/', string $domain = '', bool $secure = false, bool $httpOnly = true, bool $raw = false, ?string $sameSite = null)
    {
        // convert expiration time to a Unix timestamp
        if ($expire instanceof \DateTimeInterface) {
            $expire = $expire->format('U');
        } elseif (!is_numeric($expire)) {
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