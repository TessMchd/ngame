<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class UserController
 * @package App\Controller*
 * @Route("/profil")
 */

class UserController extends AbstractController
{
    /**
     * @Route("/", name="user_profil")
     */
    public function index(): Response
    {
        $user = $this->getUser();
        $stats=$user->getStats();
        return $this->render('user/index.html.twig', [
            'user' => $user, 'stats'=>$stats
        ]);
    }

    /**
     * @Route("/friends", name="friends")
     */
    public function Friends(
        UserRepository $userRepository
    ){
        $users = $userRepository->findAll();
        $user = $this->getUser();
        $invitUsers = [];
        foreach ($users as $invit) {
            $invitUsers[$invit->getId()] = $invit;
        }
        return $this->render('user/friends.html.twig', [
            'user' => $user, 'users' => $users,'invitations'=>$user->getInvitations(), 'invit_users'=>$invitUsers
        ]);
    }

    /**
     * @Route("/friend-request", name="friend_request")
     */
    public function sendFriendRequest(
        EntityManagerInterface $entityManager,
        Request $request,
        UserRepository $userRepository
    ){
        $user = $this->getUser();
        $friend = $userRepository->find($request->request->get('friend'));
        $invitations = $friend->getInvitations();
        if ($invitations==""){
            $invitations=array();
        }
        if (in_array($user->getId(),$invitations)){
            return $this->json(false);
        }else{
            array_push($invitations,$user->getId());
            $friend->setInvitations($invitations);
            $entityManager->flush();
        }
        return $this->json(true);
    }

    /**
     * @Route("/search-friend", name="search_friend")
     */
    public function searchFriend(
        EntityManagerInterface $entityManager,
        Request $request,
        UserRepository $userRepository
    ){
        //$friend = $userRepository->findBy(array('pseudo'=>$request->request->get('pseudo')));
        $friends=$userRepository->findByStartPseudo($request->request->get('pseudo'));
        $array=[];
        foreach ($friends as $friend){
            array_push($array,['id'=>$friend->getId(),'pseudo'=>$friend->getPseudo(),'avatar'=>$friend->getAvatar(),'rang'=>$friend->getStats()->getRang()]);
        }
        if($array!=[]) {
            return $this->json($array);
        }else{
            return $this->json(false);
        }

    }

    /**
     * @Route("/friend-accept", name="friend_accept")
     */
    public function acceptFriendRequest(
        EntityManagerInterface $entityManager,
        Request $request,
        UserRepository $userRepository
    ){
        $user = $this->getUser();
        $friend = $userRepository->find($request->request->get('friend'));
        $amisRequest = $friend->getAmis();
        $amis = $user->getAmis();
        if ($amis==""){
            $amis=array();
        }
        if ($amisRequest==""){
            $amisRequest=array();
        }
        array_push($amis,$friend->getId());
        array_push($amisRequest,$user->getId());
        $invitations=$user->getInvitations();
        unset($invitations[$user->getId()]); //je supprime la carte de ma main
        $user->setAmis($amis);
        $friend->setAmis($amisRequest);
        $user->setInvitations(null);
        $entityManager->flush();
        return $this->json(true);
    }
}
