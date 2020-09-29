<?php
/**
 * Created by IntelliJ IDEA.
 * User: GOOGLE
 * Date: 11/07/2020
 * Time: 10:43
 */

namespace App\Entity;


class Transaction
{
    private $id;
    private $status;
    private $currency_sent;
    private $amount_sent ;
    private $type ;
    private $sender_phone;
    private $sender_country_code ;
    private $recipient_country_code;
    private $recipient_phone;
    private $recipient_account_num;
    private $account_ref;
    private $created_at;
    private $last_requested_at;
    private $step;
    private $step_description;
    private $recipient_name;
    private $tmp_confirm_code_prefix;
    private $confirm_trials_nb;

    public function __construct( )
    {

    }
    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     * @return Transaction
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param mixed $status
     * @return Transaction
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCreatedAt()
    {
        return $this->created_at;
    }

    /**
     * @param mixed $created_at
     * @return Transaction
     */
    public function setCreatedAt($created_at)
    {
        $this->created_at = $created_at;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getLastRequestedAt()
    {
        return $this->last_requested_at;
    }

    /**
     * @param mixed $last_requested_at
     * @return Transaction
     */
    public function setLastRequestedAt($last_requested_at)
    {
        $this->last_requested_at = $last_requested_at;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getStep()
    {
        return $this->step;
    }

    /**
     * @param mixed $step
     * @return Transaction
     */
    public function setStep($step)
    {
        $this->step = $step;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getStepDescription()
    {
        return $this->step_description;
    }

    /**
     * @param mixed $step_description
     * @return Transaction
     */
    public function setStepDescription($step_description)
    {
        $this->step_description = $step_description;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCurrencySent()
    {
        return $this->currency_sent;
    }

    /**
     * @param mixed $currency_sent
     * @return Transaction
     */
    public function setCurrencySent($currency_sent)
    {
        $this->currency_sent = $currency_sent;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getAmountSent()
    {
        return $this->amount_sent;
    }

    /**
     * @param mixed $amount_sent
     * @return Transaction
     */
    public function setAmountSent($amount_sent)
    {
        $this->amount_sent = $amount_sent;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param mixed $type
     * @return Transaction
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getSenderPhone()
    {
        return $this->sender_phone;
    }

    /**
     * @param mixed $sender_phone
     * @return Transaction
     */
    public function setSenderPhone($sender_phone)
    {
        $this->sender_phone = $sender_phone;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getSenderCountryCode()
    {
        return $this->sender_country_code;
    }

    /**
     * @param mixed $sender_country_code
     * @return Transaction
     */
    public function setSenderCountryCode($sender_country_code)
    {
        $this->sender_country_code = $sender_country_code;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getRecipientCountryCode()
    {
        return $this->recipient_country_code;
    }

    /**
     * @param mixed $recipient_country_code
     * @return Transaction
     */
    public function setRecipientCountryCode($recipient_country_code)
    {
        $this->recipient_country_code = $recipient_country_code;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getRecipientPhone()
    {
        return $this->recipient_phone;
    }

    /**
     * @param mixed $recipient_phone
     * @return Transaction
     */
    public function setRecipientPhone($recipient_phone)
    {
        $this->recipient_phone = $recipient_phone;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getRecipientAccountNum()
    {
        return $this->recipient_account_num;
    }

    /**
     * @param mixed $recipient_account_num
     * @return Transaction
     */
    public function setRecipientAccountNum($recipient_account_num)
    {
        $this->recipient_account_num = $recipient_account_num;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getAccountRef()
    {
        return $this->account_ref;
    }

    /**
     * @param mixed $account_ref
     * @return Transaction
     */
    public function setAccountRef($account_ref)
    {
        $this->account_ref = $account_ref;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getRecipientName()
    {
        return $this->recipient_name;
    }

    /**
     * @param mixed $recipient_name
     */
    public function setRecipientName($recipient_name): void
    {
        $this->recipient_name = $recipient_name;
    }

    /**
     * @return mixed
     */
    public function getConfirmTrialsNb()
    {
        return $this->confirm_trials_nb;
    }

    /**
     * @param mixed $confirm_trials_nb
     */
    public function setConfirmTrialsNb($confirm_trials_nb): void
    {
        $this->confirm_trials_nb = $confirm_trials_nb;
    }

    /**
     * @return mixed
     */
    public function getTmpConfirmCodePrefix()
    {
        return $this->tmp_confirm_code_prefix;
    }

    /**
     * @param mixed $tmp_confirm_code_prefix
     */
    public function setTmpConfirmCodePrefix($tmp_confirm_code_prefix): void
    {
        $this->tmp_confirm_code_prefix = $tmp_confirm_code_prefix;
    }

}