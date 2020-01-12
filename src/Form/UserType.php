<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Security\Core\Security;

class UserType extends AbstractType
{
    /**
     * @var Security
     */
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        //Set the default password options
        $passwordOptions = array(
            'type' => PasswordType::class,
            'invalid_message' => 'Les deux mots de passe doivent correspondre.',
            'required' => true,
            'first_options' => ['label' => 'Mot de passe'],
            'second_options' => ['label' => 'Tapez le mot de passe à nouveau'],
            'empty_data' => '',
        );

        // If edit user : password is optional
        // User object is stored in $options['data']
        $recordId = $options['data']->getId();
        if (!empty($recordId)) {
            $passwordOptions['required'] = false;

        }

        $builder
            ->add('username', TextType::class, ['label' => "Nom d'utilisateur"])
            ->add('plainPassword', RepeatedType::class, $passwordOptions)
            ->add('email', EmailType::class, ['label' => 'Adresse email']);

        // grab the user for our event listener
        $user = $this->security->getUser();

        //add our EventListener to check if we are admin, if yes then we allow the role change
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event) use ($user) {
            //if no user logged in, we are creating so just return
            if (!$user) {
                return;
            }
            //if the logged in user is not admin, then do not add anything
            if (!in_array('ROLE_ADMIN', $user->getRoles())) {
                return;
            }
            //add our role choice
            $form = $event->getForm();
            $form->add('roles', ChoiceType::class, [
                    'label' => 'Role utilisateur',
                    'choices' => ['Admin' => 'ROLE_ADMIN', 'Utilisateur' => 'ROLE_USER'],
                    'expanded' => true,
                    'multiple' => true,
                ]
            );
        });
    }
}
