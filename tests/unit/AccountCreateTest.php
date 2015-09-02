<?php

require_once(dirname(__FILE__) . '/../_support/NostoAccountMetaDataBilling.php');
require_once(dirname(__FILE__) . '/../_support/NostoAccountMetaDataOwner.php');
require_once(dirname(__FILE__) . '/../_support/NostoAccountMetaData.php');

class AccountCreateTest extends \Codeception\TestCase\Test
{
	use \Codeception\Specify;

    /**
     * @var \UnitTester
     */
    protected $tester;

    /**
     * @inheritdoc
     */
    protected function _before()
    {
        // Configure API, Web Hooks, and OAuth client to use Mock server when testing.
        NostoApiRequest::$baseUrl = 'http://localhost:3000';
        NostoOAuthClient::$baseUrl = 'http://localhost:3000';
        NostoHttpRequest::$baseUrl = 'http://localhost:3000';
    }

	/**
	 * Tests that new accounts can be created successfully.
	 */
	public function testCreatingNewAccount()
    {
		$meta = new NostoAccountMetaData();
        $service = new NostoServiceAccount();
		$account = $service->create($meta);

		$this->specify('account was created', function() use ($account, $meta) {
			$this->assertInstanceOf('NostoAccount', $account);
			$this->assertEquals($meta->getPlatform() . '-' . $meta->getName(), $account->getName());
		});

		$this->specify('account has api token sso', function() use ($account, $meta) {
			$token = $account->getApiToken('sso');
			$this->assertInstanceOf('NostoApiToken', $token);
			$this->assertEquals('sso', $token->getName());
			$this->assertNotEmpty($token->getValue());
		});

		$this->specify('account has api token products', function() use ($account, $meta) {
			$token = $account->getApiToken('products');
			$this->assertInstanceOf('NostoApiToken', $token);
			$this->assertEquals('products', $token->getName());
			$this->assertNotEmpty($token->getValue());
		});

		$this->specify('account is connected to nosto', function() use ($account, $meta) {
			$this->assertTrue($account->isConnectedToNosto());
		});
    }

    /**
     * Tests that the service fails correctly.
     */
    public function testHttpFailure()
    {
        NostoApiRequest::$baseUrl = 'http://localhost:1234'; // not a real url

        $meta = new NostoAccountMetaData();
        $service = new NostoServiceAccount();

        $this->specify('account creation with invalid URL', function() use ($service, $meta) {
            $this->setExpectedException('NostoHttpException');
            $service->create($meta);
        });
    }
}
