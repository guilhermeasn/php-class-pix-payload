<?php

// namespace App;

/**
 * Classe para gerar o payload do qrcode estatico do PIX
 * 
 * @see https://www.bcb.gov.br/content/estabilidadefinanceira/spb_docs/ManualBRCode.pdf
 * @see https://www.bcb.gov.br/content/estabilidadefinanceira/pix/Regulamento_Pix/II_ManualdePadroesparaIniciacaodoPix.pdf
 * 
 * @author Guilherme Neves <guilhermeasn@yahoo.com.br>
 */
class PIX {

    /**
     * Constantes de configuracao padrao
     */
    const PAYLOAD_FORMAT = '01';
    const MERCHANT_ACCOUNT_GUI = 'BR.GOV.BCB.PIX';
    const MERCHANT_CATEGORY_CODE = '0000';
    const TRANSACTION_CURRENCY = '986';
    const COUNTRY_CODE = 'BR';

    /**
     *  CRC-16-CCITT-FFFF de acordo com o BACEN
     */
    const CRC16_POLYNOMIAL = 0x1021;
    const CRC16_INITIAL = 0xFFFF;

    /**
     * Chave PIX
     * 
     * *** Formatos validos ***
     * EMAIL: fulano_da_silva.recebedor@example.com
     * CPF: 12345678900
     * CNPJ: 00038166000105
     * TELEFONE: +5561912345678
     * 
     * @var string
     */
    private $key;

    /**
     * Nome de quem recebe o PIX
     *
     * @var string
     */
    private $merchant;

    /**
     * Cidade de quem recebe o PIX
     *
     * @var string
     */
    private $city;

    /**
     * Codigo para identificacao posterior do PIX
     *
     * @var string
     */
    private $code;

    /**
     * Valor do PIX
     *
     * @var string
     */
    private $amount;

    /**
     * Carrega os dados do PIX
     *
     * @param string $key
     * @param string $merchant
     * @param string $city
     * @param string $code
     * @param float $amount
     */
    public function __construct(string $key, string $merchant, string $city, string $code, float $amount) {
        
        $this->key = substr(preg_replace('/\s/is', '', $key), 0, 99);
        $this->merchant = substr(preg_replace('/[^a-z ]/is', '', $merchant), 0, 25);  # max 25 letras
        $this->city = substr(preg_replace('/[^a-z ]/is', '', $city), 0, 15);  # max 15 letras
        $this->code = substr(preg_replace('/[^a-z0-9]/is', '', $code), 0, 20);  # max 20 letras/numeros sem espacos
        $this->amount = (string) number_format($amount, 2, '.', '');
        
    }

    /**
     * Obtem todos os dados
     *
     * @return array
     */
    public function toArray() : array {
        return [
            'config' => [
                'PAYLOAD_FORMAT' => self::PAYLOAD_FORMAT,
                'MERCHANT_ACCOUNT_GUI' => self::MERCHANT_ACCOUNT_GUI,
                'MERCHANT_CATEGORY_CODE' => self::MERCHANT_CATEGORY_CODE,
                'TRANSACTION_CURRENCY' => self::TRANSACTION_CURRENCY,
                'COUNTRY_CODE' => self::COUNTRY_CODE
            ],
            'dataset' => [
                'key' => $this->key,
                'merchant' => $this->merchant,
                'city' => $this->city,
                'code' => $this->code,
                'amount' => $this->amount
            ],
            'payload' => $this->payload()
        ];
    }

    /**
     * Exibe o payload
     *
     * @return string
     */
    public function __toString() : string {
        return $this->payload();
    }

    /**
     * Quantidade de caracteres em uma informacao, retornando sempre 2 caracteres
     *
     * @param string $subject
     * @return string
     */
    private static function padlen(string $subject) : string {
        return str_pad(strlen($subject), 2, '0', STR_PAD_LEFT);
    }

    /**
     * Calcula o CRC16 e retorna os 4 caracteres principais
     *
     * @param string $subject
     * @return string
     */
    private static function CRC16(string $subject) : string {
        
        $polynomial = self::CRC16_POLYNOMIAL;
        $result = self::CRC16_INITIAL;
    
        // Checksum 
        if(($length = strlen($subject)) > 0) {
            for($offset = 0; $offset < $length; $offset++) {
                $result ^= (ord($subject[$offset]) << 8);
                for ($bitwise = 0; $bitwise < 8; $bitwise++) {
                    if(($result <<= 1) & 0x10000) $result ^= $polynomial;
                    $result &= self::CRC16_INITIAL;
                }
            }
        }
    
        // Retorna o código CRC16 de 4 caracteres
        return strtoupper(dechex($result));

    }

    /**
     * Payload Format Indicator
     * ID 00
     *
     * @return string
     */
    private function getPayloadFormat() : string {
        return '00' . self::padlen(self::PAYLOAD_FORMAT) . self::PAYLOAD_FORMAT;
    }

    /**
     * Merchant Account Information
     * ID 26
     * > GUI (ID 00)
     * > KEY (ID 01)
     *
     * @return string
     */
    private function getMerchantAccount() : string {

        $gui = '00' . self::padlen(self::MERCHANT_ACCOUNT_GUI) . self::MERCHANT_ACCOUNT_GUI;
        $key = '01' . self::padlen($this->key) . $this->key;

        return '26' . (self::padlen($gui) + self::padlen($key)) . $gui . $key;

    }

    /**
     * Merchant Category Code
     * ID 52
     *
     * @return string
     */
    private function getMerchantCategory() : string {
        return '52' . self::padlen(self::MERCHANT_CATEGORY_CODE) . self::MERCHANT_CATEGORY_CODE;
    }

    /**
     * Transaction Currency
     * ID 53
     *
     * @return string
     */
    private function getTransactionCurrency() : string {
        return '53' . self::padlen(self::TRANSACTION_CURRENCY) . self::TRANSACTION_CURRENCY;
    }

    /**
     * Transaction Amount
     * ID 54
     *
     * @return string
     */
    private function getTransactionAmount() : string {
        return '54' . self::padlen($this->amount) . $this->amount;
    }

    /**
     * Country Code
     * ID 58
     *
     * @return string
     */
    private function getCountryCode() : string {
        return '58' . self::padlen(self::COUNTRY_CODE) . self::COUNTRY_CODE;
    }

    /**
     * Merchant Name
     * ID 59
     *
     * @return string
     */
    private function getMerchantName() : string {
        return '59' . self::padlen($this->merchant) . $this->merchant;
    }

    /**
     * Merchant City
     * ID 60
     *
     * @return string
     */
    private function getMerchantCity() : string {
        return '60' . self::padlen($this->city) . $this->city;
    }

    /**
     * Additional Data Field Template
     * ID 62
     * > Reference Label (ID 05)
     *
     * @return string
     */
    private function getAdditionalData() : string {
        $label = '05' . self::padlen($this->code) . $this->code;
        return '62' . self::padlen($label) . $label;
    }

    /**
     * CRC16
     * ID 63
     *
     * @return string
     */
    private function getInitCRC16() : string {
        return '63';
    }

    /**
     * Gera o payload que pode ser usado para gerar o qrcode
     *
     * @return string
     */
    public function payload() : string {
        
        $payload = $this->getPayloadFormat()
                  .$this->getMerchantAccount()
                  .$this->getMerchantCategory()
                  .$this->getTransactionCurrency()
                  .$this->getTransactionAmount()
                  .$this->getCountryCode()
                  .$this->getMerchantName()
                  .$this->getMerchantCity()
                  .$this->getAdditionalData()
                  .$this->getInitCRC16();

        $crclen = self::padlen(self::CRC16($payload . '04'));

        return $payload . $crclen . self::CRC16($payload . $crclen);

    }

}
