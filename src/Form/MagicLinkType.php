<?php

namespace App\Form;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Regex;

class MagicLinkType extends AbstractType
{
    /** @var ParameterBagInterface */
    private $paramameters;

    public function __construct(ParameterBagInterface $parameters)
    {
        $this->paramameters = $parameters;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        return $builder
            ->add('email', EmailType::class, [
                'label'       => 'Enter your corporate email (don\'t worry, only a salted hash of it will be stored)',
                'constraints' => [
                    new Email(),
                    new Regex($this->paramameters->get('allowed_emails')),
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Send me a link',
            ]);
    }
}