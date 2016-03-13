<?php
namespace BankBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use BankBundle\Entity\User;

/**
 * @ORM\Entity
 * @ORM\Table (name = "record")
 */
class Record
{
    /**
     * @ORM\Column(type = "integer")
     * @ORM\Id
     * @ORM\GeneratedValue
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity = "User")
     * @ORM\JoinColumn(name = "user_id", referencedColumnName = "id")
     */
    private $user;

    /**
     * @ORM\Column(type = "integer")
     */
    private $amount;

    /**
     * @ORM\Column(type = "integer")
     */
    private $balance;

    /**
     * @ORM\Column(type = "datetime", name = "create_time")
     */
    private $createTime;

    public function __construct(User $user, $amount, $balance)
    {
        $this->setUser($user);
        $this->setAmount($amount);
        $this->setBalance($balance);

        $this->createTime = new \DateTime('now');
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
     * Get user
     *
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set user
     *
     * @param User
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * Get amount
     *
     * @return integer
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Set amount
     *
     * @param integer
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
    }

    /**
     * Get balance
     *
     * @return integer
     */
    public function getBalance()
    {
        return $this->balance;
    }

    /**
     * Set balance
     *
     * @param integer
     */
    public function setBalance($balance)
    {
        $this->balance = $balance;
    }

    /**
     * Get createTime
     *
     * @return datetime
     */
    public function getCreateTime()
    {
        return $this->createTime;
    }
}