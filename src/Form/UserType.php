<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bundle\SecurityBundle\Security;
class UserType extends AbstractType
{
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $currentUser = $this->security->getUser();
        $isOwnProfile = $currentUser === $options['data'];

        $builder
            ->add('email', EmailType::class)
            ->add('firstname')
            ->add('lastname');

        // Only allow role modification if the current user is an admin and either:
        // - it's their own profile, or
        // - the target user is not an admin
        if ($this->security->isGranted('ROLE_ADMIN') && 
            ($isOwnProfile || !in_array('ROLE_ADMIN', $options['data']->getRoles()))) {
            $builder->add('roles', ChoiceType::class, [
                'choices' => [
                    'User' => 'ROLE_USER',
                    'Manager' => 'ROLE_MANAGER',
                    'Admin' => 'ROLE_ADMIN'
                ],
                'multiple' => true,
                'expanded' => true,
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
} 