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
     * @Assert\GreaterThanOrEqual(value=1,message="Type must be greater than or equal to 1. 1-Rent course, 2 - Full course, 3 - Free course")
     * @Assert\LessThanOrEqual(value=3,message="Type must be less than or equal to 3. 1-Rent course, 2 - Full course, 3 - Free course")
     * @JMS\Type("int")
     */
    public $type;
    /**
     * @JMS\Type("float")
     */
    public $price;
}