<?php
namespace BankBundle\Tests\Controller;

use Liip\FunctionalTestBundle\Test\WebTestCase;

class BankControllerTest extends WebTestCase
{
    private $em;
    private $user;
    private $userRepository;
    private $recordRepository;

    protected function setUp()
    {
        self::bootKernel();
        $this->em = $this->getContainer()->get('doctrine')->getManager();

        $fixtruesPath = [
            'BankBundle\Tests\DataFixtures\ORM\LoadUserData',
            'BankBundle\Tests\DataFixtures\ORM\LoadRecordData'
        ];
        $fixtures = $this->loadFixtures($fixtruesPath)->getReferenceRepository();

        $this->user = $fixtures->getReference('user');

        $this->userRepository = $this->em->getRepository('BankBundle:User');
        $this->recordRepository = $this->em->getRepository('BankBundle:Record');
    }

    public function testNewAccount()
    {
        $client = static::createClient();

        $parameter = [
            'account' => 'NewAccount',
            'new_password' => 'Password',
            'confirm_password' => 'Password'
        ];

        $client->request('POST', '/account', $parameter);
        $jsonContent = $client->getResponse()->getContent();
        $content = json_decode($jsonContent);
        $this->assertEquals('success', $content->result);
        $this->assertEquals(2, $content->parameter->userId);

        $user = $this->userRepository->find(2);

        $this->assertEquals('NewAccount', $user->getAccount());
        $this->assertEquals('Password', $user->getPassword());
        $this->assertEquals(0, $user->getBalance());

        $this->em->remove($user);
        $this->em->flush();
    }

    public function testCheckLogin()
    {
        $client = static::createClient();

        $parameter = [
            'account' => 'test',
            'password' => 'test'
        ];

        $client->request('POST', '/login', $parameter);
        $jsonContent = $client->getResponse()->getContent();
        $content = json_decode($jsonContent);
        $this->assertEquals('success', $content->result);
        $this->assertEquals(1, $content->parameter->userId);
    }

    public function testDeposit()
    {
        $client = static::createClient();

        $url = '/user/1/deposit';
        $parameter = ['amount' => 1];

        $this->assertEquals(10000, $this->user->getBalance());


        $client->request('POST', $url, $parameter);
        $jsonContent = $client->getResponse()->getContent();
        $content = json_decode($jsonContent);
        $this->assertEquals('success', $content->result);
        $this->assertEquals('成功存款', $content->parameter->message);

        self::bootKernel();
        $em = static::$kernel->getContainer()->get('doctrine')->getManager();
        $userRepository = $em->getRepository('BankBundle:User');
        $user = $userRepository->find(1);

        $this->assertEquals(10001, $user->getBalance());

        $record = $this->recordRepository->find(4);

        $this->assertEquals(10001, $record->getBalance());

        $this->em->remove($record);
        $this->em->flush();
    }

    public function testWithdrawal()
    {
        $client = static::createClient();

        $url = '/user/1/withdrawal';
        $parameter = ['amount' => 1];
        $beforeBalance = $this->user->getBalance();

        $client->request('POST', $url, $parameter);
        $jsonContent = $client->getResponse()->getContent();
        $content = json_decode($jsonContent);
        $this->assertEquals('success', $content->result);
        $this->assertEquals('成功提款', $content->parameter->message);

        self::bootKernel();
        $em = static::$kernel->getContainer()->get('doctrine')->getManager();
        $userRepository = $em->getRepository('BankBundle:User');

        $user = $userRepository->find(1);
        $afterBalance = $user->getBalance();

        $this->assertEquals(9999, $beforeBalance - 1);

        $record = $this->recordRepository->find(4);

        $this->assertEquals($afterBalance, $record->getBalance());

        $this->em->remove($record);
        $this->em->flush();
    }


    public function testRecordInterface()
    {
        $client = static::createClient();

        $client->request('GET', '/user/1/record/interface');
        $jsonContent = $client->getResponse()->getContent();
        $content = json_decode($jsonContent);
        $this->assertEquals('success', $content->result);
        $this->assertEquals(10000, $content->parameter->balance);
        $this->assertEquals(1, $content->parameter->userId);
    }

    public function testRecordList()
    {
        $client = static::createClient();

        $client->request('GET', '/user/1/record/list');
        $jsonContent = $client->getResponse()->getContent();
        $content = json_decode($jsonContent);
        $this->assertEquals('success', $content->result);
        $this->assertEquals(3, count($content->parameter->record));
        $this->assertEquals('test', $content->parameter->account);
        $this->assertEquals(1, $content->parameter->userId);
    }

    public function testDuplicateNewAccount()
    {
        $client = static::createClient();

        $parameter = [
            'account' => $this->user->getAccount(),
            'new_password' => $this->user->getPassword(),
            'confirm_password' => $this->user->getPassword()
        ];

        $client->request('POST', '/account', $parameter);
        $jsonContent = $client->getResponse()->getContent();
        $content = json_decode($jsonContent);
        $this->assertEquals('fail', $content->result);
        $this->assertEquals('該帳號已存在', $content->parameter->message);
    }

    public function testNewAccountWithEmpty()
    {
        $client = static::createClient();

        $parameter = [
            'account' => '',
            'new_password' => '444',
            'confirm_password' => '333'
        ];

        $client->request('POST', '/account', $parameter);
        $jsonContent = $client->getResponse()->getContent();
        $content = json_decode($jsonContent);
        $this->assertEquals('fail', $content->result);
        $this->assertEquals('帳號不得為空白', $content->parameter->message);
    }

    public function testNewAccountWithBlank()
    {
        $client = static::createClient();

        $parameter =  [
            'account' => 'w w',
            'new_password' => '444',
            'confirm_password' => '333'
        ];

        $client->request('POST', '/account', $parameter);
        $jsonContent = $client->getResponse()->getContent();
        $content = json_decode($jsonContent);
        $this->assertEquals('fail', $content->result);
        $this->assertEquals('帳號不得含有空白', $content->parameter->message);
    }

    public function testNewAccountConfirmFail()
    {
        $client = static::createClient();

        $parameter = [
            'account' => 'testNewAccountConfirmFail',
            'new_password' => '444',
            'confirm_password' => '333'
        ];

        $client->request('POST', '/account', $parameter);
        $jsonContent = $client->getResponse()->getContent();
        $content = json_decode($jsonContent);
        $this->assertEquals('fail', $content->result);
        $this->assertEquals('新密碼與再次輸入密碼不一樣!', $content->parameter->message);
    }

    public function testNewAccountPasswordIsEmpty()
    {
        $client = static::createClient();

        $parameter = [
            'account' => 'testNewAccountPasswordIsEmpty',
            'new_password' => '',
            'confirm_password' => ''
        ];

        $client->request('POST', '/account', $parameter);
        $jsonContent = $client->getResponse()->getContent();
        $content = json_decode($jsonContent);
        $this->assertEquals('fail', $content->result);
        $this->assertEquals('密碼不得為空白', $content->parameter->message);
    }

    public function testNewAccountPriority()
    {
        $client = static::createClient();

        $parameter = [
            'account' => 'testNewAccountPriority',
            'new_password' => '',
            'confirm_password' => '123'
        ];

        $client->request('POST', '/account', $parameter);
        $jsonContent = $client->getResponse()->getContent();
        $content = json_decode($jsonContent);
        $this->assertEquals('fail', $content->result);
        $this->assertEquals('新密碼與再次輸入密碼不一樣!', $content->parameter->message);
    }

    public function testNewAccountWithSlash()
    {
        $client = static::createClient();

        $parameter = [
            'account' => 'testNewAccountWithSlash',
            'new_password' => '1////',
            'confirm_password' => '/////'
        ];

        $client->request('POST', '/account', $parameter);
        $jsonContent = $client->getResponse()->getContent();
        $content = json_decode($jsonContent);
        $this->assertEquals('fail', $content->result);
        $this->assertEquals('新密碼與再次輸入密碼不一樣!', $content->parameter->message);
    }

    public function testCheckLoginIsEmpty()
    {
        $client = static::createClient();

        $parameter = [
            'account' => null,
            'password' => null
        ];

        $client->request('POST', '/login', $parameter);
        $jsonContent = $client->getResponse()->getContent();
        $content = json_decode($jsonContent);
        $this->assertEquals('fail', $content->result);
        $this->assertEquals('密碼或帳號輸入錯誤', $content->parameter->message);
    }

    public function testCheckLoginAccountIsEmpty()
    {
        $client = static::createClient();

        $parameter = [
            'account' => null,
            'password' => '/qwe232'
        ];

        $client->request('POST', '/login', $parameter);
        $jsonContent = $client->getResponse()->getContent();
        $content = json_decode($jsonContent);
        $this->assertEquals('fail', $content->result);
        $this->assertEquals('密碼或帳號輸入錯誤', $content->parameter->message);
    }

    public function testCheckLoginPasswordIsEmpty()
    {
        $client = static::createClient();

        $parameter = [
            'account' => '123',
            'password' => null
        ];

        $client->request('POST', '/login', $parameter);
        $jsonContent = $client->getResponse()->getContent();
        $content = json_decode($jsonContent);
        $this->assertEquals('fail', $content->result);
        $this->assertEquals('密碼或帳號輸入錯誤', $content->parameter->message);
    }

    public function testDepositWithFloat()
    {
        $client = static::createClient();

        $parameter = ['amount' => '0.1'];

        $client->request('POST', '/user/1/deposit', $parameter);
        $jsonContent = $client->getResponse()->getContent();
        $content = json_decode($jsonContent);
        $this->assertEquals('fail', $content->result);
        $this->assertEquals('不能有小數', $content->parameter->message);
    }

    public function testDepositIsEmpty()
    {
        $client = static::createClient();

        $parameter = ['amount' => ''];

        $client->request('POST', '/user/1/deposit', $parameter);
        $jsonContent = $client->getResponse()->getContent();
        $content = json_decode($jsonContent);
        $this->assertEquals('fail', $content->result);
        $this->assertEquals('不能為空白', $content->parameter->message);
    }

    public function testDepositIsNagativeNumber()
    {
        $client = static::createClient();

        $parameter = ['amount' => '-1'];

        $client->request('POST', '/user/1/deposit', $parameter);
        $jsonContent = $client->getResponse()->getContent();
        $content = json_decode($jsonContent);
        $this->assertEquals('fail', $content->result);
        $this->assertEquals('只能存比0元大的金額', $content->parameter->message);
    }

    public function testDepositWithCharacter()
    {
        $client = static::createClient();

        $parameter = ['amount' => 'aaaa'];

        $client->request('POST', '/user/1/deposit', $parameter);
        $jsonContent = $client->getResponse()->getContent();
        $content = json_decode($jsonContent);
        $this->assertEquals('fail', $content->result);
        $this->assertEquals('只能輸入數字', $content->parameter->message);
    }

    public function testDepositMax()
    {
        $client = static::createClient();

        $parameter = ['amount' => '10000001'];

        $client->request('POST', '/user/1/deposit', $parameter);
        $jsonContent = $client->getResponse()->getContent();
        $content = json_decode($jsonContent);
        $this->assertEquals('fail', $content->result);
        $this->assertEquals('最多只能存10000000', $content->parameter->message);
    }

    public function testDepositOverflow()
    {
        $client = static::createClient();

        $parameter = ['amount' => '999999999999999999999999999'];

        $client->request('POST', '/user/1/deposit', $parameter);
        $jsonContent = $client->getResponse()->getContent();
        $content = json_decode($jsonContent);
        $this->assertEquals('fail', $content->result);
        $this->assertEquals('最多只能存10000000', $content->parameter->message);
    }

    public function testWithdrawalWithFloat()
    {
        $client = static::createClient();

        $parameter = ['amount' => '0.1'];

        $client->request('POST', '/user/1/withdrawal', $parameter);
        $jsonContent = $client->getResponse()->getContent();
        $content = json_decode($jsonContent);
        $this->assertEquals('fail', $content->result);
        $this->assertEquals('不能有小數', $content->parameter->message);
    }

    public function testWithdrawalIsEmpty()
    {
        $client = static::createClient();

        $parameter = ['amount' => ''];

        $client->request('POST', '/user/1/withdrawal', $parameter);
        $jsonContent = $client->getResponse()->getContent();
        $content = json_decode($jsonContent);
        $this->assertEquals('fail', $content->result);
        $this->assertEquals('不能為空白', $content->parameter->message);
    }

    public function testWithdrawalIsNagativeNumber()
    {
        $client = static::createClient();

        $parameter = ['amount' => '-1'];

        $client->request('POST', '/user/1/withdrawal', $parameter);
        $jsonContent = $client->getResponse()->getContent();
        $content = json_decode($jsonContent);
        $this->assertEquals('fail', $content->result);
        $this->assertEquals('只能提比0元大的金額', $content->parameter->message);
    }

    public function testWithdrawalIsWithCharacter()
    {
        $client = static::createClient();

        $parameter = ['amount' => 'aaaa'];

        $client->request('POST', '/user/1/withdrawal', $parameter);
        $jsonContent = $client->getResponse()->getContent();
        $content = json_decode($jsonContent);
        $this->assertEquals('fail', $content->result);
        $this->assertEquals('只能輸入數字', $content->parameter->message);
    }

    public function testWithdrawalInsufficientBalance()
    {
        $client = static::createClient();

        $parameter = ['amount' => '9999999999'];

        $client->request('POST', '/user/1/withdrawal', $parameter);
        $jsonContent = $client->getResponse()->getContent();
        $content = json_decode($jsonContent);
        $this->assertEquals('fail', $content->result);
        $this->assertEquals('餘額不足', $content->parameter->message);
    }

    protected function tearDown()
    {
        parent::tearDown();

        $this->em->remove($this->user);
        $this->em->flush();

        $this->em->close();
    }
}
