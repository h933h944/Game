<?php
namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass = "AppBundle\Repository\MessageRepository")
 * @ORM\Table(name = "message")
 */
class Message
{
    /**
     * @ORM\Column(type = "integer")
     * @ORM\Id
     * @ORM\GeneratedValue
     */
    private $id;


    /**
     * @ORM\OneToMany(targetEntity="Message", mappedBy="reply")
     */
    private $message;

    /**
     * @ORM\Column(type = "string", length = 1000)
     */
    private $content;

    /**
     * @ORM\Column(type = "string", length = 30)
     */
    private $name;

    /**
     * @ORM\Column(type = "datetime")
     */
    private $createtime;

    /**
     * @ORM\ManyToOne(targetEntity = "Message", inversedBy="message")
     * @ORM\JoinColumn(name = "reply_id", referencedColumnName = "id")
     */
    private $reply;

    public function __construct($name, $content)
    {
        $this->id = new \Doctrine\Common\Collections\ArrayCollection();
        $this->name = $name;
        $this->content = $content;
        $this->createtime = new \DateTime('now');
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set content
     *
     * @param string $content
     */
    public function setContent($content)
    {
        $this->content = $content;
    }

    /**
     * Get content
     *
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set name
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get createtime
     *
     * @return DateTime
     */
    public function getCreatetime()
    {
        return $this->createtime;
    }

    /**
     * Set replyId
     *
     * @param \AppBundle\Entity\Message $replyId
     */
    public function setReplyId($replyId)
    {
        $this->replyId = $replyId;
    }

    /**
     * Get replyId
     *
     * @return \AppBundle\Entity\Message
     */
    public function getReplyId()
    {
        return $this->replyId;
    }

    /**
     * Set createtime
     *
     * @param \DateTime $createtime
     *
     * @return Message
     */
    public function setCreatetime($createtime)
    {
        $this->createtime = $createtime;

        return $this;
    }
}
