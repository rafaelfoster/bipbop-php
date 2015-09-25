<?php

namespace BIPBOP;

/**
 * Web Service - Implementação do BIPBOP WS
 * @author Desenvolvimento <desenvolvimento@bipbop.com.br>
 */
class WebService {

    const ENDPOINT = "https://irql.bipbop.com.br/";
    const REFERRER = "https://juridicocorrespondentes.com.br/";
    const PARAMETER_QUERY = "q";
    const PARAMETER_APIKEY = "apiKey";

    protected $apiKey;
    protected $resource;

    /**
     * Inicializa a API
     * @param string $apiKey Chave de acesso da BIPBOP
     */
    public function __construct($apiKey) {
        $this->resource = curl_init(self::ENDPOINT);
        $this->apiKey = $apiKey;

        curl_setopt_array($this->resource, [
            CURLOPT_REFERER => self::REFERRER,
            CURLOPT_HEADER => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_FOLLOWLOCATION => true,
        ]);
    }

    /**
     * Realiza a chamada ao WS da BIPBOP
     * @param string $query Query da BIPBOP
     * @param array $parameters
     * @return \DOMDocument
     */
    public function post($query, Array $parameters) {
        curl_setopt_array($this->resource, [
            CURLOPT_POSTFIELDS => array_merge($parameters, [
                self::PARAMETER_QUERY => $query,
                self::PARAMETER_APIKEY => $this->apiKey
            ])
        ]);

        $dom = new \DOMDocument;
        $ret = curl_exec($this->resource);
        $dom->loadXML($ret);
        static::assert($dom);
        return $dom;
    }

    /**
     * Realiza um assertion do documento
     * @param \DOMDocument $dom
     */
    public static function assert(\DOMDocument $dom) {
        $queryNode = (new \DOMXPath($dom))->query("/BPQL/header/exception");
        if ($queryNode->length) {
            $nodeException = $queryNode->item(0);
            $source = $nodeException->getAttribute("source");
            $code = $nodeException->getAttribute("code");
            $id = $nodeException->getAttribute("id");
            $message = $nodeException->nodeValue;
            throw new Exception(sprintf("[%s:%s/%s] %s", $code, $source, $id, $message), $code);
        }
    }

    /**
     * Fecha o recurso HTTP após o uso
     */
    public function __destruct() {
        curl_close($this->resource);
    }

}
