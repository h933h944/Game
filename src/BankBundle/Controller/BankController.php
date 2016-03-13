<?php
namespace BankBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use BankBundle\Entity\User;

/**
 * @Route("/")
 */
class BankController extends Controller
{
    /**
     * Add deposit
     *
     * @Route("/user/{userId}/deposit", name = "deposit")
     * @Method("POST")
     * @param Request $request
     * @param string $userId
     */
    public function depositAction(Request $request, $userId)
    {
        $post = $request->request;

        $maxAmount = 10000000;
        $amount = $post->get('amount');

        $client = $this->container->get('snc_redis.default');

        if ($client->get('user:1:balance') === null) {
            $this->loadDataToRedis();
        }

        $userBalanceKey = 'user:' . $userId . ':balance';

        try {
            if (floor($amount) != $amount) {
                throw new \Exception('不能有小數');
            }

            if ($amount == null) {
                throw new \Exception('不能為空白');
            }

            if (!is_numeric($amount)) {
                throw new \Exception('只能輸入數字');
            }

            if ($amount <= '0') {
                throw new \Exception('只能存比0元大的金額');
            }

            if ($amount > $maxAmount) {
                throw new \Exception('最多只能存' . $maxAmount);
            }

            $client->incrby($userBalanceKey, $amount);
            $client->lpush('updateUser', $userId);

            $record = [
                'userId' => $userId,
                'amount' => $amount,
                'balance' => $client->get($userBalanceKey)
            ];

            $jsonRecord = json_encode($record);
            $client->lpush('jsonRecordList', $jsonRecord);

            $result = 'success';
            $parameter = [
                'userId' => $userId,
                'message' => '成功存款'
            ];

        } catch (\Exception $exception) {
            $result = 'fail';
            $parameter = [
                'userId' => $userId,
                'message' => $exception->getMessage()
            ];
        }

        $parameterArray = [
            'result' => $result,
            'parameter' => $parameter
        ];

        return new JsonResponse($parameterArray);
    }

    /**
     * @Route("/user/{userId}/withdrawal", name = "withdrawal")
     * @Method("POST")
     * @param Request $request
     * @param string $userId
     */
    public function withdrawalAction(Request $request, $userId)
    {
        $client = $this->container->get('snc_redis.default');

        if ($client->get('user:1:balance') === null) {
            $this->loadDataToRedis();
        }

        $post = $request->request;
        $amount = $post->get('amount');

        $userBalanceKey = 'user:' . $userId . ':balance';

        try {
            if (floor($amount) != $amount) {
                throw new \Exception('不能有小數');
            }

            if ($amount == null) {
                throw new \Exception('不能為空白');
            }

            if (!is_numeric($amount)) {
                throw new \Exception('只能輸入數字');
            }

            if ($amount <= '0') {
                throw new \Exception('只能提比0元大的金額');
            }

            $amount = $amount * -1;
            $client->incrby($userBalanceKey, $amount);

            if ($client->get($userBalanceKey)) {
                throw new \Exception('餘額不足');
            }

            $client->lpush('updateUser', $userId);

            $record = [
                'userId' => $userId,
                'amount' => $amount,
                'balance' => $client->get($userBalanceKey)
            ];

            $jsonRecord = json_encode($record);

            $client->lpush('jsonRecordList', $jsonRecord);

            $result = 'success';
            $parameter = [
                'userId' => $userId,
                'message' => '成功提款'
            ];

        } catch (\Exception $exception) {
            $result = 'fail';
            $parameter = [
                'userId' => $userId,
                'message' => $exception->getMessage()
            ];
        }

        $parameterArray = [
            'result' => $result,
            'parameter' => $parameter
        ];

        return new JsonResponse($parameterArray);
    }

    /**
     * @Route("/user/{userId}/record/list", name = "showRecordList")
     * @Method("GET")
     * @param Request $request
     * @param string $userId
     */
    public function showRecordListAction(Request $request, $userId)
    {
        $em = $this->getDoctrine()->getManager();

        $recordRepository = $em->getRepository('BankBundle:Record');
        $userRepository = $em->getRepository('BankBundle:User');

        $user = $userRepository->find($userId);
        $record = $recordRepository->findBy(['user' => $user]);

        $recordArrayList = [];

        foreach ($record as $perRecord) {
            $recordArray = $perRecord->toArray();
            array_push($recordArrayList, $recordArray);
        }

        $account = $user->getAccount();

        $parameter = [
            'record' => $recordArrayList,
            'account' => $account,
            'userId' => $userId
        ];

        $parameterArray = [
            'result' => 'success',
            'parameter' => $parameter
        ];

        return new JsonResponse($parameterArray);
    }

    /**
     * @Route("/account", name = "checkNewAccount")
     * @Method("POST")
     * @param Request $request
     */
    public function checkNewAccountAction(Request $request)
    {
        $post = $request->request;
        $em = $this->getDoctrine()->getManager();

        $repository = $em->getRepository('BankBundle:User');

        $account = $post->get('account');
        $password = $post->get('new_password');
        $confirmPassword = $post->get('confirm_password');

        try {
            if ($account == null) {
                throw new \Exception('帳號不得為空白');
            }

            if (strpbrk($account, ' ')) {
                throw new \Exception('帳號不得含有空白');
            }

            if ($password !== $confirmPassword) {
                throw new \Exception('新密碼與再次輸入密碼不一樣!');
            }

            if ($password == null) {
                throw new \Exception('密碼不得為空白');
            }

            $userOfDB = $repository->findBy(['account' => $account]);

            if ($userOfDB) {
                throw new \Exception('該帳號已存在');
            }

            $user = new User($account, $password);

            $em->persist($user);
            $em->flush();
            $em->clear();

            $result = 'success';
            $userId = $user->getId();
            $parameter = ['userId' => $userId];

        } catch (\Exception $exception) {
            $result = 'fail';
            $parameter = ['message' => $exception->getMessage()];
        }

        $parameterArray = [
            'result' => $result,
            'parameter' => $parameter
        ];

        return new JsonResponse($parameterArray);
    }

    /**
     * @Route("/user/{userId}/record/interface", name = "showRecordInterface")
     * @Method("GET")
     * @param Request request
     * @param string $userId
     */
    public function showRecordInterfaceAction(Request $request, $userId)
    {
        $em = $this->getDoctrine()->getManager();

        $repository = $em->getRepository('BankBundle:User');

        $user = $repository->find($userId);

        $balance = $user->getBalance();

        $parameter = [
            'balance' => $balance,
            'userId' => $userId,
        ];

        $parameterArray = [
            'result' => 'success',
            'parameter' => $parameter
        ];

        return new JsonResponse($parameterArray);
    }

    /**
     * @Route("/login", name = "checkLogin")
     * @Method("POST")
     * @param Request request
     */
    public function checkLoginAction(Request $request)
    {
        $post = $request->request;
        $em = $this->getDoctrine()->getManager();

        $account = $post->get("account");
        $password = $post->get("password");

        $userRepository = $em->getRepository('BankBundle:User');
        $user = $userRepository->findOneBy([
            'account' => $account,
            'password' => $password
        ]);

        try {
            if ($user == null) {
                throw new \Exception('密碼或帳號輸入錯誤');
            }

            $result = 'success';
            $userId = $user->getId();
            $parameter = ['userId' => $userId];

        } catch (\Exception $exception) {
            $result = 'fail';
            $parameter = ['message' => $exception->getMessage()];
        }

        $parameterArray = [
            'result' => $result,
            'parameter' => $parameter
        ];

        return new JsonResponse($parameterArray);
    }

    public function loadDataToRedis()
    {
        $client = $this->container->get('snc_redis.default');
        $em = $this->getDoctrine()->getManager();
        $userRepository = $em->getRepository('BankBundle:User');

        $userArray = $userRepository->findAll();
        foreach ($userArray as $user) {
            $key = 'user:' . $user->getId() . ':balance';
            $value = $user->getBalance();

            $client->set($key, $value);
        }
    }
}
