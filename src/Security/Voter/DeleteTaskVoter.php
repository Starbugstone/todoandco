<?php


namespace App\Security\Voter;


use App\Entity\AnonymousUser;
use App\Entity\Task;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class DeleteTaskVoter extends Voter
{

    const DELETE_TASK = 'deleteTask';

    /**
     * @inheritDoc
     */
    protected function supports($attribute, $subject)
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, [self::DELETE_TASK])) {
            return false;
        }

        // only vote on Task objects inside this voter
        if (!$subject instanceof Task) {
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

        /* @var task $task */
        $task = $subject;

        if ($user === $task->getUser()){
            return true;
        }

        if ($task->getUser() instanceof AnonymousUser && $user->isAdmin()) {
            return true;
        }
        return false;
    }
}