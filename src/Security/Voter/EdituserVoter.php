<?php


namespace App\Security\Voter;


use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class EdituserVoter extends Voter
{
    const EDIT_USER = 'editUser';

    /**
     * @inheritDoc
     */
    protected function supports($attribute, $subject)
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, [self::EDIT_USER])) {
            return false;
        }

        // only vote on user objects inside this voter
        if (!$subject instanceof User) {
            return false;
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();
        if (!$user instanceof User) {
            // the user must be logged in; if not, deny access
            return false;
        }

        //if we are admin then we can edit
        if (in_array('ROLE_ADMIN', $user->getRoles())) {
            return true;
        }

        // you know $subject is a User object, thanks to supports
        /** @var User $editedUser */
        $editedUser = $subject;

        //we can edit our own user
        if ($user === $editedUser) {
            return true;
        }

        //none of the above, so we can't edit
        return false;
    }

}