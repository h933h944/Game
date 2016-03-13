<?php
namespace BankBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name = "user")
 */
class User
{
    /**
     * @ORM\Column(type = "integer")
     * @ORM\Id
     * @ORM\GeneratedValue
     */
    private $id;

    /**
     * @ORM\Column(type = "string", length = 30, unique = true)
     */
    private $account;

    /**
     * @ORM\Column(type = "string", length = 40)
     */
    private $password;

    /**
     * @ORM\Column(type = "integer")
     */
    private $balance;

    /**
     * @ORM\Version
     * @ORM\Column(type = "integer")
     */
    private $version;

    public function __construct($account, $password, $balance = 0)
    {
        $this->setAccount($account);
        $this->setPassword($password);
        $this->setBalance($balance);
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
     * Get account
     *
     * @return string
     */
    public function getAccount()
    {
        return $this->account;
    }

    /**
     * Set account
     *
     * @param string
     */
    public function setAccount($account)
    {
        $this->account = $account;
    }

    /**
     * Get password
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set password
     *
     * @param string
     */
    public function setPassword($password)
    {
        $this->password = $password;
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
     * Get version
     *
     * @return integer
     */
    public function getVersion($version)
    {
        $this->version = $version;
    }
}
