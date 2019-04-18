<?php
namespace ArkonEvent\CodeReadr\ApiClient;

use Http\Discovery\HttpClientDiscovery;
use Http\Discovery\MessageFactoryDiscovery;
use Http\Client\HttpClient;
use Http\Discovery\Psr17FactoryDiscovery;
use Http\Message\MessageFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use ArkonEvent\CodeReadr\Exceptions\CodeReadrApiException;

/**
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED.
 * IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */
/**
 * Simple HTTP client for use against CodeReadr API
 *
 * @license https://opensource.org/licenses/MIT
 */
class Client
{

    /**
     *
     * @var \Http\Client\HttpClient
     */
    protected $httpClient;

    /**
     *
     * @var \Http\Discovery\MessageFactory
     */
    protected $messageFactory;

    /**
     *
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     *
     * @var string
     */
    protected $apiKey;

    private const BASE_URL_API = 'https://api.codereadr.com/api/';

    public const SECTION_USERS = 'users';

    public const SECTION_DEVICES = 'devices';

    public const SECTION_SCAN_PROPERTIES = 'scan_properties';

    public const SECTION_SERVICES = 'services';

    public const SECTION_DATABASES = 'databases';

    public const SECTION_LIMITS = 'limits';

    public const ACTION_RETREIVE = 'retrieve';

    public const ACTION_CREATE = 'create';

    public const ACTION_UPDATE = 'update';

    public const ACTION_DELETE = 'delete';

    public const ACTION_VALIDATE = 'validate';

    public const ACTION_AVAILABLE = 'available';

    public const ACTION_ADD_QUESTION = 'addquestion';

    public const ACTION_REMOVE_QUESTION = 'removequestion';

    public const ACTION_ADD_USER_PERMISSION = 'adduserpermission';

    public const ACTION_REVOKE_USER_PERMISSION = 'revokeuserpermission';

    public const API_TIME_ZONE = 'America/New_York';

    /**
     * Client constructor.
     * @param string $apiKey
     * @param LoggerInterface|null $logger
     * @param HttpClient|null $httpClient
     */
    public function __construct(string $apiKey, ?LoggerInterface $logger = null, ?HttpClient $httpClient = null)
    {
        $this->apiKey = $apiKey;
        $this->setLogger($logger);
        $this->setHttpClient($httpClient);
    }

    /**
     * Execute a request against the CodeReadr API, this method will throw a CodeReadrApiException if an error is returned from the API (as opposed to the API behaviour which returns 200 regardless)
     *
     * @param string $section
     * @param string $action
     * @param array $params
     * @return \SimpleXMLElement
     * @throws CodeReadrApiException
     * @throws \Http\Client\Exception
     */
    public function request(string $section, string $action, array $params = []): \SimpleXMLElement
    {
        $data = [
            'api_key' => $this->apiKey,
            'section' => $section,
            'action' => $action
        ];
        $data = array_merge($data, $params);
        
        $request = $this->getMessageFactory()->createRequest('POST', self::BASE_URL_API, [
            'Content-type' => 'application/x-www-form-urlencoded'
        ], http_build_query($data));
        
        $response = $this->httpClient->sendRequest($request);
        
        // Check status code and throw exception if error occures
        $responseBody = (string) $response->getBody();
        $xml = new \SimpleXMLElement($responseBody);
        $status = (int) $xml->status;
        
        if ($status != 1) {
            throw new CodeReadrApiException($xml->error);
        }
        return $xml;
    }

    /**
     * Set HTTP client, if none is given autodetection is attempted
     *
     * @param HttpClient $httpClient            
     */
    public function setHttpClient(?HttpClient $httpClient = null): void
    {
        if (is_null($httpClient)) {
            $this->httpClient = HttpClientDiscovery::find();
            if (! is_null($this->logger)) {
                $this->logger->debug('HttpClientDiscovery::find() used to find HTTP client in ' . __CLASS__);
            }
        } else {
            $this->httpClient = $httpClient;
        }
    }

    /**
     * @param LoggerInterface|null $logger
     */
    public function setLogger(?LoggerInterface $logger = null): void
    {
        $this->logger = $logger;
    }

    /**
     * Create a message factory
     *
     * @return \Http\Message\MessageFactory
     */
    protected function getMessageFactory(): MessageFactory
    {
        if (is_null($this->messageFactory)) {
            //@todo upgrade to Psr17FactoryDiscovery
            $this->messageFactory = MessageFactoryDiscovery::find();
        }

        return $this->messageFactory;
    }
}