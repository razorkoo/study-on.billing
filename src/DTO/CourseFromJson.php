<?php
namespace App\DTO;

use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

class CourseFromJson
{
    /**
     * @Assert\NotBlank(message="Blank code")
     * @JMS\Type("string")
     */
    public $code;
    /**
     * @Assert\NotBlank(message="Blank type")
     * @Assert\Choice(choices={"rent","full","free"},message="Type should be one of these types: rent,full,free")
     * @JMS\Type("string")
     */
    public $type;
    /**
     * @JMS\Type("float")
     */
    public $price;
}