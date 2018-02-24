<?php

namespace RudyMas\Emvc_Controller;

use Exception;
use RudyMas\XML_JSON\XML_JSON;
use Twig_Environment;
use Twig_Loader_Filesystem;

/**
 * Class Controller (PHP version 7.1)
 *
 * @author      Rudy Mas <rudy.mas@rmsoft.be>
 * @copyright   2016-2018, rmsoft.be. (http://www.rmsoft.be/)
 * @license     https://opensource.org/licenses/GPL-3.0 GNU General Public License, version 3 (GPL-3.0)
 * @version     1.4.2
 * @package     Library
 */
class Emvc_Controller
{
    /**
     * @param null|string $page
     * @param array $data
     * @param string $type
     * @param int $httpResponseCode
     * @throws Exception
     */
    public function render(?string $page, array $data, string $type, int $httpResponseCode = 200): void
    {
        switch (strtoupper($type)) {
            case 'HTML':
                $this->renderHTML($page);
                exit;
            case 'JSON':
                $this->renderJSON($data, $httpResponseCode);
                exit;
            case 'XML':
                $this->renderXML($data, $httpResponseCode);
                exit;
            case 'PHP':
                $this->renderPHP($page, $data);
                exit;
            case 'TWIG':
                $this->renderTWIG($page, $data, false); //TODO: Set debug to tre while coding
                exit;
            default:
                throw new Exception("<p><b>Exception:</b> Wrong page type ({$type}) given.</p>", 501);
        }
    }

    /**
     * @param string $page Page to redirect to (Can be an URL or a routing directive)
     */
    public function redirect(string $page): void
    {
        if (preg_match("/(http|ftp|https)?:?\/\//", $page)) {
            header('Location: ' . $page);
        } else {
            $dirname = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');
            header('Location: ' . $dirname . $page);
        }
        exit;
    }

    /**
     * @param string $page HTML page to output to the browser
     */
    private function renderHTML(string $page): void
    {
        $display = $_SERVER['DOCUMENT_ROOT'] . BASE_URL . '/../src/views/' . $page;
        if (file_exists($display)) {
            readfile($display);
        } else {
            header('HTTP/1.1 404 Not Found');
        }
    }

    /**
     * @param array $data Array of data following XML standards
     * @param int $httpResponseCode HTTP response code to send (Default: 200)
     */
    private function renderJSON(array $data, int $httpResponseCode = 200): void
    {
        if ($httpResponseCode >= 200 && $httpResponseCode <= 206) {
            $jsonData = $data;
        } else {
            $jsonData['error']['code'] = $httpResponseCode;
            $jsonData['error']['message'] = 'Error ' . $httpResponseCode . ' has occurred';
        }

        $convert = new XML_JSON();
        $convert->setArrayData($jsonData);
        $convert->array2json();

        http_response_code($httpResponseCode);
        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Headers: *');
        print($convert->getJsonData());
    }

    /**
     * @param array $data Array of data following XML standards
     * @param int $httpResponseCode HTTP response code to send (Default: 200)
     */
    private function renderJSONData(array $data, int $httpResponseCode = 200): void
    {
        if ($httpResponseCode >= 200 && $httpResponseCode <= 206) {
            $jsonData['data'] = $data;
        } else {
            $jsonData['error']['code'] = $httpResponseCode;
            $jsonData['error']['message'] = 'Error ' . $httpResponseCode . ' has occurred';
        }

        $convert = new XML_JSON();
        $convert->setArrayData($jsonData);
        $convert->array2json();

        http_response_code($httpResponseCode);
        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Headers: *');
        print($convert->getJsonData());
    }

    /**
     * @param array $data Array of data following XML standards
     * @param int $httpResponseCode HTTP response code to send (Default: 200)
     */
    private function renderXML(array $data, int $httpResponseCode = 200): void
    {
        if ($httpResponseCode >= 200 && $httpResponseCode <= 206) {
            $xmlData = $data;
        } else {
            $xmlData['error']['code'] = $httpResponseCode;
            $xmlData['error']['message'] = 'Error ' . $httpResponseCode . ' has occurred';
        }

        $convert = new XML_JSON();
        $convert->setArrayData($xmlData);
        $convert->array2xml('members');

        http_response_code($httpResponseCode);
        header('Content-Type: application/xml; charset=utf-8');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Headers: *');
        print($convert->getXmlData());
    }

    /**
     * @param string $page Name of the HTML5 view class
     * @param array $data array of data to insert on the page
     */
    private function renderPHP(string $page, array $data): void
    {
        list($view, $subpage) = $this->processPhpPage($page);
        if ($subpage == null) {
            new $view($data);
        } else {
            $subpage .= 'Page';
            $loadPage = new $view($data);
            $loadPage->$subpage();
        }
    }

    /**
     * @param string $page
     * @return array
     */
    private function processPhpPage(string $page): array
    {
        $view = '\\View';
        $split = explode(':', trim($page, '/'));
        if (count($split) > 1) $subpage = $split[1]; else $subpage = null;
        $class = explode('/', trim($split[0], '/'));
        foreach ($class as $path) {
            $view .= "\\{$path}";
        }
        return [$view, $subpage];
    }

    /**
     * @param string $page
     * @param array $data
     * @param bool $debug
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    private function renderTWIG(string $page, array $data, bool $debug = false): void
    {
        $loader = new Twig_Loader_Filesystem('src/views');
        $twig = new Twig_Environment($loader, ['debug' => $debug]);
        if ($debug === true) $twig->addExtension(new \Twig_Extension_Debug());
        $twig->display($page, $data);
    }

    /**
     * @param mixed $array
     */
    public function checkArray($array): void
    {
        print('<pre>');
        print_r($array);
        print('</pre>');
    }
}

/** End of File: Emvc_Controller.php **/