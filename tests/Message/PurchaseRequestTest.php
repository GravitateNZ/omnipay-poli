<?php
namespace Omnipay\Poli;

use Omnipay\Common\CreditCard;
use Omnipay\Poli\Message\PurchaseRequest;
use Omnipay\Tests\TestCase;

class PurchaseRequestTest extends TestCase
{
    protected $request;

    public function setUp(): void
    {
        $this->request = new PurchaseRequest($this->getHttpClient(), $this->getHttpRequest());
    }

    public function testFailureURL()
    {
        $params = array(
            'amount' => '12.00',
            'description' => 'Test Product',
            'currency' => 'NZD',
            'merchantCode' => 'ASDF',
            'authenticationCode' => '1234',
            'transactionId' => 123,
            'returnUrl' => 'https://www.example.com/return',
            'cancelUrl' => 'https://www.example.com/cancel',
        );
        $this->request->initialize($params);

        // if failure URL is not provided, return return URL
        $data = $this->request->getData();
        $this->assertSame("https://www.example.com/return", $data['FailureURL']);
        $this->assertSame(null, $this->request->getFailureURL());

        // if failure URL is set, return what is set
        $this->request->setFailureURL("https://www.fails.com");
        $data = $this->request->getData();
        $this->assertSame("https://www.fails.com", $data['FailureURL']);
        $this->assertSame("https://www.fails.com", $this->request->getFailureURL());
    }

    /**
     * check that MerchantRef outputs correctly, based on supplied params
     */
    public function testMerchantRef()
    {
        $params = array(
            'amount' => '12.00',
            'description' => 'Test Product',
            'currency' => 'NZD',
            'merchantCode' => 'ASDF',
            'authenticationCode' => '1234',
            'transactionId' => 123,
            'returnUrl' => 'https://www.example.com/return',
            'cancelUrl' => 'https://www.example.com/cancel',
            );
        $this->request->initialize($params);

        $this->request->setMerchantReference("MerchantRef");
        $this->assertEquals("MerchantRef", $this->request->getMerchantReference());

        //no card means use standard form
        $this->assertEquals(123, $this->request->getCombinedMerchantRef());
        $data = $this->request->getData();
        $this->assertEquals("MerchantRef", $data['MerchantReference']);
        $this->assertEquals(1, $data['MerchantReferenceFormat']);

        //valid card uses combined form
        $params['card'] = $this->getValidCard();
        $this->request->initialize($params);
        $this->assertEquals("Example User||123", $this->request->getCombinedMerchantRef());

        //missing name means use standard form
        unset($params['card']['firstName']);
        unset($params['card']['lastName']);
        $this->request->initialize($params);
        // var_dump($params['card']);
        $this->assertEquals(123, $this->request->getCombinedMerchantRef());
    }
}
