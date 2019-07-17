<?php
namespace App\DTO;

use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

class BillingUserFromModel
{
    /**
     * @Assert\NotBlank(message="Blank email")
     * @Assert\Email(message="Wrong email format")
     * @JMS\Type("string")
     */
    public $email;
    /**
     * @Assert\NotBlank(message="Blank password")
     * @Assert\Length(min=6, minMessage="Password must be at least 6 symbols")
     * @JMS\Type("string")
     */
    public $password;
}