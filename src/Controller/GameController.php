<?php

namespace App\Controller;

use App\Entity\Game;
use App\Entity\Round;
use App\Repository\CardRepository;
use App\Repository\GameRepository;
use App\Repository\RoundRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class GameController extends AbstractController
{
    /**
     * @Route("/new-game", name="new_game")
     */
    public function newGame(
        UserRepository $userRepository,
        GameRepository $gameRepostory
    ): Response {
        $users = $userRepository->findAll();
        $user = $this->getUser();
        $games=[];
        $revanches=[];
        $ouvertes=[];
        $tUsers = [];
        foreach ($users as $u) {
            $tUsers[$u->getId()] = $u;
        }
        $en_cours = $gameRepostory->findby(array('user1'=>$user->getId()));
        foreach ($en_cours as $game){
            if ($game->getUser2() != null) {
                if ($game->getEnded() == "") {
                    array_push($games, $game);
                } else {
                    array_push($revanches, $game);
                }
            }
        }

        $en_cours = $gameRepostory->findby(array('user2'=>$user->getId()));
        foreach ($en_cours as $game){
            if($game->getEnded()=="") {
                array_push($games, $game);
            }else{

                array_push($revanches,$game);
            }
        }

        $ouv = $gameRepostory->findOuvertes($user->getId());
        foreach ($ouv as $ov){
            array_push($ouvertes, $ov);
        }


        return $this->render('game/index.html.twig', [
            'users' => $users,
            'en_cours'=> $games,
            'revanches'=> $revanches,
            'ouvertes'=> $ouvertes,
            'tusers'=> $tUsers

        ]);
    }

    /**
     * @Route("/create-game", name="create_game")
     */
    public function createGame(
        Request $request,
        EntityManagerInterface $entityManager,
        UserRepository $userRepository,
        CardRepository $cardRepository
    ): Response
    {

            $user1 = $this->getUser();
        if ($request->request->get('user2') != "") {
            $user2 = $userRepository->find($request->request->get('user2'));
        }else{
            $user2=null;
        }

            if ($user1 !== $user2) {
                $game = new Game();
                $game->setUser1($user1);
                $game->setUser2($user2);
                $game->setCreated(new \DateTime('now'));

                $entityManager->persist($game);

                $set = new Round();
                $set->setGame($game);
                $set->setCreated(new \DateTime('now'));
                $set->setSetNumber(1);

                $cards = $cardRepository->findAll();
                $tCards = [];
                foreach ($cards as $card) {
                    $tCards[$card->getId()] = $card;

                }
                shuffle($tCards);
                $carte = array_pop($tCards);
                $set->setRemovedCard($carte->getId());

                $tMainJ1 = [];
                $tMainJ2 = [];
                for ($i = 0; $i < 6; $i++) {
                    //on distribue 6 cartes aux deux joueurs
                    $carte = array_pop($tCards);
                    $tMainJ1[] = $carte->getId();
                    $carte = array_pop($tCards);
                    $tMainJ2[] = $carte->getId();
                }
                $set->setUser1HandCards($tMainJ1);
                $set->setUser2HandCards($tMainJ2);

                $tPioche = [];

                foreach ($tCards as $card) {
                    $carte = array_pop($tCards);
                    $tPioche[] = $carte->getId();
                }
                $set->setPioche($tPioche);
                $set->setUser1Action([
                    'SECRET' => false,
                    'DEPOT' => false,
                    'OFFRE' => false,
                    'ECHANGE' => false
                ]);

                $set->setUser2Action([
                    'SECRET' => false,
                    'DEPOT' => false,
                    'OFFRE' => false,
                    'ECHANGE' => false
                ]);

                $set->setBoard([
                    'EMPL1' => ['N'],
                    'EMPL2' => ['N'],
                    'EMPL3' => ['N'],
                    'EMPL4' => ['N'],
                    'EMPL5' => ['N'],
                    'EMPL6' => ['N'],
                    'EMPL7' => ['N']
                ]);
                $entityManager->persist($set);
                $entityManager->flush();

                return $this->redirectToRoute('show_game', [
                    'game' => $game->getId()
                ]);
            } else {
                return $this->redirectToRoute('new_game');
            }
    }

    /**
     * @Route("/show-game/{game}", name="show_game")
     */
    public function showGame(
        CardRepository $cardRepository,
        Game $game
    ): Response {
        $cards = $cardRepository->findAll();
        $tCards = [];
        foreach ($cards as $card) {
            $tCards[$card->getId()] = $card;
        }

        return $this->render('game/show_game.html.twig', [
            'game' => $game,
            'set' => $game->getSets()[0],
            'cards' => $tCards
        ]);
    }

    /**
     * @Route("/get-tour-game/{game}", name="get_tour")
     */
    public function getTour(
        Game $game
    ): Response {
        if ($this->getUser()->getId() === $game->getUser1()->getId() && $game->getQuiJoue() === 1) {
            return $this->json(true);
        }

        if ($this->getUser()->getId() === $game->getUser2()->getId() && $game->getQuiJoue() === 2) {
            return $this->json(true);
        }

        return $this->json( false);
    }

    /**
     * @Route("/change-player/{game}", name="change_player")
     */
    public function ChangePlayer(
        Game $game,
        EntityManagerInterface $entityManager
    ): Response {
        if ($game->getQuiJoue()==1){
            $game->setQuiJoue(2);
            $game->getSets()[0]->setUser2Pioche(0);
        }else{
            $game->setQuiJoue(1);
            $game->getSets()[0]->setUser1Pioche(0);
        }

        $entityManager->persist($game);
        $entityManager->flush();

        return $this->json(true);
    }

    /**
     * @Route("/pioche/{game}", name="pioche")
     */
    public function Pioche(
        Game $game,
        EntityManagerInterface $entityManager,
        CardRepository $cardRepository
    ): Response {
        if ($this->getUser()->getId() === $game->getUser1()->getId()) {
            if($game->getSets()[0]->getUser1Pioche()== 0 and (sizeof($game->getSets()[0]->getPioche()))>0 ){
                $game->getSets()[0]->setUser1Pioche(1);
                $hands=$game->getSets()[0]->getUser1HandCards();
                $carte=$game->getSets()[0]->getPioche()[(sizeof($game->getSets()[0]->getPioche()))-1] ;
                array_push($hands, $carte);
                $game->getSets()[0]->setUser1HandCards($hands);
                $pioche=$game->getSets()[0]->getPioche();
                unset($pioche[(sizeof($game->getSets()[0]->getPioche()))-1]);
                $game->getSets()[0]->setPioche($pioche);
                $entityManager->persist($game->getSets()[0]);
                $entityManager->flush();
                $response=$cardRepository->findBy(array('id'=>$carte));
                return $this->json([$response[0]->getId(),$response[0]->getPicture()]);
            }

        } elseif ($this->getUser()->getId() === $game->getUser2()->getId() and (sizeof($game->getSets()[0]->getPioche()))>0 ) {
            if ($game->getSets()[0]->getUser2Pioche() == 0) {
                $game->getSets()[0]->setUser2Pioche(1);
                $hands=$game->getSets()[0]->getUser2HandCards();
                $carte=$game->getSets()[0]->getPioche()[(sizeof($game->getSets()[0]->getPioche()))-1] ;
                array_push($hands, $carte);
                $game->getSets()[0]->setUser2HandCards($hands);
                $pioche=$game->getSets()[0]->getPioche();
                unset($pioche[(sizeof($game->getSets()[0]->getPioche()))-1]);
                $game->getSets()[0]->setPioche($pioche);
                $entityManager->persist($game->getSets()[0]);
                $entityManager->flush();
                $response=$cardRepository->findBy(array('id'=>$carte));
                return $this->json([$response[0]->getId(),$response[0]->getPicture()]);
            }
        }

        return $this->json(true);

    }

    /**
     * @param Game $game
     * @route("/refresh/{game}", name="refresh_plateau_game")
     */
    public function refreshPlateauGame(CardRepository $cardRepository, Game $game)
    {
        $cards = $cardRepository->findAll();
        $tCards = [];
        foreach ($cards as $card) {
            $tCards[$card->getId()] = $card;
        }
        if ($this->getUser()->getId() === $game->getUser1()->getId()) {
            $moi['handCards'] = $game->getSets()[0]->getUser1HandCards();
            $moi['actions'] = $game->getSets()[0]->getUser1Action();
            $moi['board'] = $game->getSets()[0]->getUser1BoardCards();
            $adversaire['handCards'] = $game->getSets()[0]->getUser2HandCards();
            $adversaire['actions'] = $game->getSets()[0]->getUser2Action();
            $adversaire['board'] = $game->getSets()[0]->getUser2BoardCards();
            $player=$game->getUser2();
        } elseif ($this->getUser()->getId() === $game->getUser2()->getId()) {
            $moi['handCards'] = $game->getSets()[0]->getUser2HandCards();
            $moi['actions'] = $game->getSets()[0]->getUser2Action();
            $moi['board'] = $game->getSets()[0]->getUser2BoardCards();
            $adversaire['handCards'] = $game->getSets()[0]->getUser1HandCards();
            $adversaire['actions'] = $game->getSets()[0]->getUser1Action();
            $adversaire['board'] = $game->getSets()[0]->getUser1BoardCards();
            $player=$game->getUser1();
        } else {
            return $this->redirectToRoute('user_profil');
        }

        return $this->render('game/plateau_game.html.twig', [
            'game' => $game,
            'set' => $game->getSets()[0],
            'cards' => $tCards,
            'moi' => $moi,
            'adversaire' => $adversaire,
            'player' => $player
        ]);
    }

    /**
     * @Route("/action-game/{game}", name="action_game")
     */
    public function actionGame(
        EntityManagerInterface $entityManager,
        Request $request, Game $game){


        $action = $request->request->get('action');
        $user = $this->getUser();
        $round = $game->getSets()[0]; //a gérer selon le round en cours

        if ($game->getUser1()->getId() === $user->getId())
        {
            $joueur = 1;
        } elseif ($game->getUser2()->getId() === $user->getId()) {
            $joueur = 2;
        } else {
            /// On a un problème... On pourrait rediriger vers une page d'erreur.
        }

        switch ($action) {
            case 'secret':
                $carte = $request->request->get('carte');
                if ($joueur === 1) {
                    $actions = $round->getUser1Action(); //un tableau...
                    $actions['SECRET'] = [$carte]; //je sauvegarde la carte cachée dans mes actions
                    $round->setUser1Action($actions); //je mets à jour le tableau
                    $main = $round->getUser1HandCards();
                    $indexCarte = array_search($carte, $main); //je récupère l'index de la carte a supprimer dans ma main
                    unset($main[$indexCarte]); //je supprime la carte de ma main
                    $round->setUser1HandCards($main);
                }
                else if ($joueur === 2) {
                    $actions = $round->getUser2Action(); //un tableau...
                    $actions['SECRET'] = [$carte]; //je sauvegarde la carte cachée dans mes actions
                    $round->setUser2Action($actions); //je mets à jour le tableau
                    $main = $round->getUser2HandCards();
                    $indexCarte = array_search($carte, $main); //je récupère l'index de la carte a supprimer dans ma main
                    unset($main[$indexCarte]); //je supprime la carte de ma main
                    $round->setUser2HandCards($main);
                }
                break;

            case 'depot':
                $carte1 = $request->request->get('carte1');
                $carte2=$request->request->get('carte2');
                if ($joueur === 1) {

                    $actions = $round->getUser1Action(); //un tableau...
                    $actions['DEPOT'] = [[$carte1,$carte2]]; //je sauvegarde la carte cachée dans mes actions
                    $round->setUser1Action($actions); //je mets à jour le tableau
                    $main = $round->getUser1HandCards();
                    $indexCarte = array_search($carte1, $main); //je récupère l'index de la carte a supprimer dans ma main
                    unset($main[$indexCarte]); //je supprime la carte de ma main
                    $indexCarte = array_search($carte2, $main); //je récupère l'index de la carte a supprimer dans ma main
                    unset($main[$indexCarte]); //je supprime la carte de ma main
                    $round->setUser1HandCards($main);
                }
                if ($joueur === 2) {
                    $actions = $round->getUser2Action(); //un tableau...
                    $actions['DEPOT'] = [[$carte1,$carte2]]; //je sauvegarde la carte cachée dans mes actions
                    $round->setUser2Action($actions); //je mets à jour le tableau
                    $main = $round->getUser2HandCards();
                    $indexCarte = array_search($carte1, $main); //je récupère l'index de la carte a supprimer dans ma main
                    unset($main[$indexCarte]); //je supprime la carte de ma main
                    $indexCarte = array_search($carte2, $main); //je récupère l'index de la carte a supprimer dans ma main
                    unset($main[$indexCarte]); //je supprime la carte de ma main
                    $round->setUser2HandCards($main);
                }
                break;
        }

        $entityManager->flush();
        return $this->json([true,$action]);

    }

    /**
     * @Route("/delete-game/{game}", name="delete_game")
     */
    public function deleteGame(
        EntityManagerInterface $entityManager,
        Game $game
        ): Response {
        $entityManager->remove($game);
        $entityManager->flush();

        return $this->redirectToRoute('new_game');
    }

    /**
     * @Route("/join-game/{game}", name="join_game")
     */
    public function joinGame(
        EntityManagerInterface $entityManager,
        Game $game
    ): Response{
        if($game->getUser2()== null ){
            $user = $this->getUser();
            $game->setUser2($user);
            $entityManager->flush();
            return $this->redirectToRoute('show_game', [
                'game' => $game->getId()
            ]);
        }else{
            return $this->redirectToRoute('new_game');
        }
    }
}

