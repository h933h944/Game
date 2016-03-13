<?php
namespace AppBundle\Controller;

use AppBundle\Entity\Message;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

/**
 * @Route("/")
 */
class MessageController extends Controller
{
    /**
     * @Route ("/message", name = "showMessage")
     * @Method("GET")
     */
    public function showMessageAction()
    {
        $doctrine = $this->getDoctrine();

        $messageRepository = $doctrine->getRepository('AppBundle:Message');

        $msg = $messageRepository->findMessage();
        $replyResult = $messageRepository->findReply($msg);

        $twig = 'AppBundle:board:board.html.twig';
        $parameter = [
            'msg' => $msg,
            'replyResult' => $replyResult
        ];

        $template = $this->render($twig, $parameter);

        return $template;
    }

    /**
     * @Route ("/message", name = "createMessage")
     * @Method("POST")
     * @param Request $request
     */
    public function createMessageAction(Request $request)
    {
        $post = $request->request;
        $doctrine = $this->getDoctrine();

        $name = $post->get('name');
        $content = $post->get('content');

        if ($this->isEmpty($name)) {
            $queryResult = 'name could not empty.';
            $twig = 'AppBundle:board:index.html.twig';
            $parameter = ['queryResult' => $queryResult];

            $template = $this->render($twig, $parameter);

            return $template;
        }

        if ($this->isEmpty($content)) {
            $queryResult = 'Content could not empty.';
            $twig = 'AppBundle:board:index.html.twig';
            $parameter = ['queryResult' => $queryResult];

            $template = $this->render($twig, $parameter);

            return $template;
        }

        $em = $doctrine->getManager();
        $messageObject = new Message($name, $content);

        $em->persist($messageObject);
        $em->flush();
        $em->clear();

        $queryResult = 'You have to leave a message.';
        $twig = 'AppBundle:board:index.html.twig';
        $parameter = ['queryResult' => $queryResult];

        $template = $this->render($twig, $parameter);

        return $template;
    }

    /**
     * @Route ("/message/Reply", name = "createReply")
     * @Method("POST")
     * @param Request $request
     */
    public function createReplyAction(Request $request)
    {
        $post = $request->request;
        $doctrine = $this->getDoctrine();

        $replyName = $post->get('reply_name');
        $replyMsg = $post->get('reply_msg');

        if ($this->isEmpty($replyName)) {
            $queryResult = 'name could not empty.';
            $twig = 'AppBundle:board:index.html.twig';
            $parameter = ['queryResult' => $queryResult];

            $template = $this->render($twig, $parameter);

            return $template;
        }

        if ($this->isEmpty($replyMsg)) {
            $queryResult = 'Content could not empty.';
            $twig = 'AppBundle:board:index.html.twig';
            $parameter = ['queryResult' => $queryResult];

            $template = $this->render($twig, $parameter);

            return $template;
        }

        $em = $doctrine->getManager();

        $name = $post->get('reply_name');
        $content = $post->get('reply_msg');
        $messageId = $post->get('message_id');
        $entity = 'AppBundle\Entity\Message';

        $reply = new Message($name, $content);

        $messageOfReply = $em->find($entity, $messageId);
        $reply->setReplyId($messageOfReply);

        $reply->getId()->add($messageOfReply);

        $em->persist($reply);
        $em->flush();
        $em->clear();

        $queryResult = 'You have to leave a Reply.';
        $twig = 'AppBundle:board:index.html.twig';
        $parameter = ['queryResult' => $queryResult];

        $template = $this->render($twig, $parameter);

        return $template;
    }

    /**
     * @Route ("/messageD", name = "deleteMessage")
     * @param Request $request
     */
    public function deleteMessageAction(Request $request)
    {
        $post = $request->request;
        $doctrine = $this->getDoctrine();

        $messageId = $post->get('message_id');
        $repository = $doctrine->getRepository('AppBundle:Message');

        $repository->deleteReply($messageId);
        $repository->deleteMessage($messageId);

        $queryResult = 'Your message was deleted!';
        $twig = 'AppBundle:board:index.html.twig';
        $parameter = ['queryResult' => $queryResult];

        $template = $this->render($twig, $parameter);

        return $template;
    }

    /**
     * @Route ("/messageR", name = "updateMessage")
     * @param Request $request
     */
    public function updateMessageAction(Request $request)
    {
        $post = $request->request;
        $doctrine = $this->getDoctrine();

        $messageId = $post->get('message_id');
        $content = $post->get('content');

        if ($this->isEmpty($content)) {
            $queryResult = 'Content could not empty.';
            $twig = 'AppBundle:board:index.html.twig';
            $parameter =['queryResult' => $queryResult];

            $template = $this->render($twig, $parameter);

            return $template;
        }

        $repository = $doctrine->getRepository('AppBundle:Message');

        $repository->updateMessage($messageId, $content);

        $queryResult = 'Your message was updated!';
        $twig = 'AppBundle:board:index.html.twig';
        $parameter = ['queryResult' => $queryResult];

        $template = $this->render($twig, $parameter);

        return $template;
    }

    /**
     * @Route ("/message/alter_area", name = "clickAlter")
     * @Method("GET")
     * @param Request $request
     */
    public function clickAlterAction()
    {
        $doctrine = $this->getDoctrine();

        $messageRepository = $doctrine->getRepository('AppBundle:Message');

        $msg = $messageRepository->findMessage();
        $replyResult = $messageRepository->findReply($msg);

        $twig = 'AppBundle:board:board.html.twig';
        $parameter = [
            'msg' => $msg,
            'replyResult' => $replyResult
        ];

        $template = $this->render($twig, $parameter);

        return $template;
    }

    /**
     * @Route ("/message/reply_area", name = "clickReply")
     * @Method("GET")
     * @param Request $request
     */
    public function clickReplyAction()
    {
        $doctrine = $this->getDoctrine();

        $messageRepository = $doctrine->getRepository('AppBundle:Message');

        $msg = $messageRepository->findMessage();
        $replyResult = $messageRepository->findReply($msg);

        $twig = 'AppBundle:board:board.html.twig';
        $parameter = [
            'msg' => $msg,
            'replyResult' => $replyResult
        ];

        $template = $this->render($twig, $parameter);

        return $template;
    }

    /**
     * 辨識是否為空白
     * @param string $string
     * @return boolean
     */
    public function isEmpty($string)
    {
        if (empty($string) && $string != '0') {
            return true;
        }

        return false;
    }
}
