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
        $rangs=[
            1 => 'Vagabond puant du quartier',
            2 => 'Brigand du vieux port',
            3 => 'Pirate à influence mineure',
            4 => "Petite frappe de l'ombre",
            5 => 'Supernovae des mers',
            6 => 'Grand Corsaire',
            7 => "Courtier de l'ombre",
            8 => 'Légende endormier',
            9 => 'Empereur',
            10 => 'Roi des Pirates'
        ];
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
            'tusers'=> $tUsers,
            'rangs'=>$rangs

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

                $set->setUser1BoardCards([
                    'jambe'=>[],
                    'balais'=>[],
                    'rhum'=>[],
                    'perroquet'=>[],
                    'longuevue'=>[],
                    'boulet'=>[],
                    'sabre'=>[]

                ]);

                $set->setUser2BoardCards([
                    'jambe'=>[],
                    'balais'=>[],
                    'rhum'=>[],
                    'perroquet'=>[],
                    'longuevue'=>[],
                    'boulet'=>[],
                    'sabre'=>[]

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
            'set' => $game->getSets()[count($game->getSets())-1],
            'cards' => $tCards
        ]);
    }
    /**
     * @Route("/check-adversaire/{game}", name="check_adversaire")
     */
    public function isUser2( Game $game
    ): Response {
        if($game->getUser2()!=null){
            return $this->json(true);
        }
        else{
            return $this->json(false);
        }
    }

    /**
     * @Route("/get-tour-game/{game}", name="get_tour")
     */
    public function getTour(
        Game $game,EntityManagerInterface $entityManager
    ): Response {
        $round = $game->getSets()[count($game->getSets())-1];
        $actions1=$round->getUser1Action();
        $actions2=$round->getUser2Action();
        if($actions1['OFFRE']=="done" and $actions1['DEPOT']==true and $actions1['SECRET'] and $actions1['ECHANGE']=="done"){
            if($actions2['OFFRE']=="done" and $actions2['DEPOT']==true and $actions2['SECRET'] and $actions2['ECHANGE']=="done") {
                if ($this->getUser()->getId() === $game->getUser1()->getId() ) {
                    $round->setEnd1(1);
                }
                if ($this->getUser()->getId() === $game->getUser2()->getId() ) {
                    $round->setEnd2(1);
                }
                $entityManager->flush();
                return $this->json('ended');
            }

        }
        if($game->getUser2()==null){
            return $this->json('ouverte');
        }

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
        EntityManagerInterface $entityManager,
        Request $request
    ): Response {
        $pioche= $request->request->get('pioche');
        $round = $game->getSets()[count($game->getSets())-1];
        if ($game->getQuiJoue()==1){
            $game->setQuiJoue(2);
            if($pioche == 1) {
                $round->setUser2Pioche(0);
            }else{
                $round->setUser2Pioche(1);
            }
        }else{
            $game->setQuiJoue(1);
            if($pioche == 1) {
                $round->setUser1Pioche(0);
            }else{
                $round->setUser1Pioche(1);
            }
        }

        $entityManager->persist($game);
        $entityManager->flush();

        return $this->json(true);
    }

    /**
     * @Route("/end-game/{game}", name="end_game")
     */
    public function endGame(
        Game $game,
        EntityManagerInterface $entityManager,
        CardRepository $cardRepository
    ): Response {
        $round = $game->getSets()[count($game->getSets())-1];
        $actions1=$round->getUser1Action();
        $actions2=$round->getUser2Action();

        if ($actions1['SECRET']=='done'){
            return $this->json('done');
        }else{
            if ($this->getUser()->getId() === $game->getUser1()->getId()) {
                $moi_secret=$actions1['SECRET'][0];
                $adv_secret=$actions2['SECRET'][0];
                $carte= $cardRepository->find($moi_secret);


            } elseif ($this->getUser()->getId() === $game->getUser2()->getId()) {
                $moi_secret=$actions2['SECRET'][0];
                $adv_secret=$actions1['SECRET'][0];
                $carte= $cardRepository->find($moi_secret);


            }   else {
                return $this->redirectToRoute('user_profil');
            }


            $board1=$round->getUser1BoardCards();
            $board2=$round->getUser2BoardCards();
            $carte_secret1=$cardRepository->find($actions1['SECRET'][0]);
            array_push($board1[$carte_secret1->getName()],$carte_secret1->getId());
            $carte_secret2=$cardRepository->find($actions2['SECRET'][0]);
            array_push($board2[$carte_secret2->getName()],$carte_secret2->getId());
            $actions1['SECRET']="done";
            $actions2['SECRET']="done";
            $round->setUser1BoardCards($board1);
            $round->setUser2BoardCards($board2);
            $round->setUser1Action($actions1);
            $round->setUser2Action($actions2);
            $entityManager->flush();
            return $this->json([$carte->getName(),$cardRepository->find($adv_secret)->getName()]);
        }

    }

    /**
     * @Route("/win/{game}", name="win")
     */
    public function win(Game $game, EntityManagerInterface $entityManager, CardRepository $cardRepository
    ): Response {
        $round = $game->getSets()[count($game->getSets())-1];
        if ($round->getEnded()== "") {
            $round->setEnded(new \DateTime('now'));
            $entityManager->flush();
        }
        $board1=$round->getUser1BoardCards();
        $board2=$round->getUser2BoardCards();
        $score1=0;
        $score2=0;
        $jetons_gagnes1=0;
        $jetons_gagnes2=0;
        $winner=0;
        $win_card1=[];
        $win_card2=[];

        foreach ($board1 as $key => $value){
            if(count($board1[$key]) > count($board2[$key])){
                $point= $cardRepository->find($value[0]);
                $score1+=$point->getNumber();
                $jetons_gagnes1+=1;
                array_push($win_card1,$point->getName());
            }else if (count($board2[$key]) > count($board1[$key])) {
                $point= $cardRepository->find($board2[$key][0]);
                $score2+=$point->getNumber();
                $jetons_gagnes2+=1;
                array_push($win_card2,$point->getName());
            }
        }
        if($score1>=11 or $score2>=11) {
            $game->setEnded(new \DateTime('now'));
            if ($score1 >= 11) {
                $winner=1;
            } else{
                $winner=2;
            }
        }else if ($jetons_gagnes1>=4 or $jetons_gagnes2>=4){
            $game->setEnded(new \DateTime('now'));
            if ($jetons_gagnes1>=4) {
                $winner=1;
            } else{
                $winner=2;
            }
        }
        $stats1=$game->getUser1()->getStats();
        $stats2=$game->getUser2()->getStats();

        if($winner!=0){
          if($winner==1){
              $game->setWinner($game->getUser1());
              if ($this->getUser()->getId() === $game->getUser1()->getId()) {
                      $defaites = $stats2->getDefaites();
                      $stats2->setDefaites($defaites + 1);
                      $victoires = $stats1->getVictoires();
                      $stats1->setVictoires($victoires + 1);
                      $stats1->setPieces($stats1->getPieces()+$jetons_gagnes1);
                      $stats2->setPieces($stats2->getPieces()+$jetons_gagnes2);
                      $pj=$stats1->getPartiesJouees();
                      $stats1->setPartiesJouees($pj+1);
                      $pj=$stats2->getPartiesJouees();
                      $stats2->setPartiesJouees($pj+1);
                      if ($stats1->getGrade() < 10) {
                          $stats1->setGrade($stats1->getGrade() + 1);
                      }
                      if ($stats2->getGrade() > 1) {
                          $stats2->setGrade($stats2->getGrade() - 1);
                      }
                  }

          }else{
              $game->setWinner($game->getUser2());
              if ($this->getUser()->getId() === $game->getUser1()->getId()) {
                  $stats1->setDefaites($stats1->getDefaites()+1);
                  $stats2->setVictoires($stats2->getVictoires()+1);
                  $stats1->setPieces($stats1->getPieces()+$jetons_gagnes1);
                  $stats2->setPieces($stats2->getPieces()+$jetons_gagnes2);
                  $pj=$stats1->getPartiesJouees();
                  $stats1->setPartiesJouees($pj+1);
                  $pj=$stats2->getPartiesJouees();
                  $stats2->setPartiesJouees($pj+1);
              }
              if($stats2->getGrade()<10){
                  $stats2->getGrade($stats2->getGrade()+1);
              }
              if ($stats1->getGrade() > 1) {
                  $stats1->setGrade($stats1->getGrade() - 1);
              }
          }
        }


        if ($this->getUser()->getId() === $game->getUser1()->getId()) {
            $mes_cartes= $win_card1;
            $adv_cartes=$win_card2;


        } elseif ($this->getUser()->getId() === $game->getUser2()->getId()) {
            $mes_cartes= $win_card2;
            $adv_cartes=$win_card1;
        }

        $entityManager->flush();

        if($winner!=0){
            $game->setEnded(new \DateTime('now'));
            $entityManager->flush();
            return $this->json([$game->getWinner()->getId(),[$mes_cartes,$adv_cartes],$this->getUser()->getId()]);
        }else{

            return $this->json([0,[$mes_cartes,$adv_cartes],$this->getUser()->getId()]);
        }

    }
    /**
     * @Route("/next-round/{game}", name="next_round")
     */
    public function nextRound(Game $game, EntityManagerInterface $entityManager, CardRepository $cardRepository
    ): Response {
        $round = $game->getSets()[count($game->getSets())-1];
        if($round->getEnd1()==1 and $round->getEnd2()==1) {
            if($round->getSetNumber() <= 3){
                if ($this->getUser()->getId() === $game->getUser1()->getId()) {
                    $set = new Round();
                    $set->setGame($game);
                    $set->setCreated(new \DateTime('now'));
                    $set->setSetNumber($set->getSetNumber() + 1);

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

                    $set->setUser1BoardCards([
                        'jambe' => [],
                        'balais' => [],
                        'rhum' => [],
                        'perroquet' => [],
                        'longuevue' => [],
                        'boulet' => [],
                        'sabre' => []

                    ]);

                    $set->setUser2BoardCards([
                        'jambe' => [],
                        'balais' => [],
                        'rhum' => [],
                        'perroquet' => [],
                        'longuevue' => [],
                        'boulet' => [],
                        'sabre' => []

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

                } else {
                    return $this->json(false);
                }
            }else{
                return $this->json(false);
            }

            return $this->json(true);
        }
    }

    /**
     * @Route("/pioche/{game}", name="pioche")
     */
    public function Pioche(
        Game $game,
        EntityManagerInterface $entityManager,
        CardRepository $cardRepository
    ): Response {
        $round = $game->getSets()[count($game->getSets())-1];
        if ($this->getUser()->getId() === $game->getUser1()->getId()) {
            if($round->getUser1Pioche()== 0 and (sizeof($round->getPioche()))>0 ){
                $round->setUser1Pioche(1);
                $hands=$round->getUser1HandCards();
                $carte=$round->getPioche()[(sizeof($round->getPioche()))-1] ;
                array_push($hands, $carte);
                $round->setUser1HandCards($hands);
                $pioche=$round->getPioche();
                unset($pioche[(sizeof($round->getPioche()))-1]);
                $round->setPioche($pioche);
                $entityManager->persist($round);
                $entityManager->flush();
                $response=$cardRepository->findBy(array('id'=>$carte));
                return $this->json([$response[0]->getId(),$response[0]->getPicture()]);
            }

        } elseif ($this->getUser()->getId() === $game->getUser2()->getId() and (sizeof($round->getPioche()))>0 ) {
            if ($round->getUser2Pioche() == 0) {
                $round->setUser2Pioche(1);
                $hands=$round->getUser2HandCards();
                $carte=$round->getPioche()[(sizeof($round->getPioche()))-1] ;
                array_push($hands, $carte);
                $round->setUser2HandCards($hands);
                $pioche=$round->getPioche();
                unset($pioche[(sizeof($round->getPioche()))-1]);
                $round->setPioche($pioche);
                $entityManager->persist($round);
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
        $round = $game->getSets()[count($game->getSets())-1];

        if ($this->getUser()->getId() === $game->getUser1()->getId()) {
            $moi['handCards'] = $round->getUser1HandCards();
            $moi['actions'] = $round->getUser1Action();
            $moi['board'] = $round->getUser1BoardCards();
            $adversaire['handCards'] = $round->getUser2HandCards();
            $adversaire['actions'] = $round->getUser2Action();
            $adversaire['board'] = $round->getUser2BoardCards();
            $player=$game->getUser2();
        } elseif ($this->getUser()->getId() === $game->getUser2()->getId()) {
            $moi['handCards'] = $round->getUser2HandCards();
            $moi['actions'] = $round->getUser2Action();
            $moi['board'] = $round->getUser2BoardCards();
            $adversaire['handCards'] = $round->getUser1HandCards();
            $adversaire['actions'] = $round->getUser1Action();
            $adversaire['board'] = $round->getUser1BoardCards();
            $player=$game->getUser1();
        } else {
            return $this->redirectToRoute('user_profil');
        }

        return $this->render('game/plateau_game.html.twig', [
            'game' => $game,
            'set' => $round,
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
        $round = $game->getSets()[count($game->getSets())-1]; //a gérer selon le round en cours

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

            case 'offre' :
                $carte1 = $request->request->get('carte1');
                $carte2=$request->request->get('carte2');
                $carte3 = $request->request->get('carte3');
                if ($joueur === 1) {
                    $actions = $round->getUser1Action();
                    $actions['OFFRE'] = [$carte1,$carte2,$carte3];
                    $round->setUser1Action($actions);
                    $main = $round->getUser1HandCards();
                    $indexCarte = array_search($carte1, $main);
                    unset($main[$indexCarte]);
                    $indexCarte = array_search($carte2, $main);
                    unset($main[$indexCarte]);
                    $indexCarte = array_search($carte3, $main);
                    unset($main[$indexCarte]);
                    $round->setUser1HandCards($main);
                }
                if ($joueur === 2) {
                    $actions = $round->getUser2Action();
                    $actions['OFFRE'] = [$carte1,$carte2,$carte3];
                    $round->setUser2Action($actions);
                    $main = $round->getUser2HandCards();
                    $indexCarte = array_search($carte1, $main);
                    unset($main[$indexCarte]);
                    $indexCarte = array_search($carte2, $main);
                    unset($main[$indexCarte]);
                    $indexCarte = array_search($carte3, $main);
                    unset($main[$indexCarte]);
                    $round->setUser2HandCards($main);
                }
                break;
            case 'echange' :
                $fpair = $request->request->get('firstpair');
                $spair = $request->request->get('secondpair');
                if ($joueur === 1) {
                    $actions = $round->getUser1Action();
                    $actions['ECHANGE'] = [$fpair,$spair];
                    $round->setUser1Action($actions);
                    $main = $round->getUser1HandCards();
                    $indexCarte = array_search($fpair[0], $main);
                    unset($main[$indexCarte]);
                    $indexCarte = array_search($fpair[1], $main);
                    unset($main[$indexCarte]);
                    $indexCarte = array_search($spair[0], $main);
                    unset($main[$indexCarte]);
                    $indexCarte = array_search($spair[1], $main);
                    unset($main[$indexCarte]);
                    $round->setUser1HandCards($main);
                }
                if ($joueur === 2) {
                    $actions = $round->getUser2Action();
                    $actions['ECHANGE'] = [$fpair,$spair];
                    $round->setUser2Action($actions);
                    $main = $round->getUser2HandCards();
                    $indexCarte = array_search($fpair[0], $main);
                    unset($main[$indexCarte]);
                    $indexCarte = array_search($fpair[1], $main);
                    unset($main[$indexCarte]);
                    $indexCarte = array_search($spair[0], $main);
                    unset($main[$indexCarte]);
                    $indexCarte = array_search($spair[1], $main);
                    unset($main[$indexCarte]);
                    $round->setUser2HandCards($main);
                }

        }

        $entityManager->flush();
        return $this->json([true,$action]);

    }

    /**
     * @Route("/update-offre/{game}", name="update_offre")
     */
    public function updateOffre(
        EntityManagerInterface $entityManager,
        Request $request,
        CardRepository $cardRepository,
        Game $game
    ): Response {
        $user = $this->getUser();
        $round = $game->getSets()[count($game->getSets())-1];
        $carte = $request->request->get('carte');

        if ($game->getUser1()->getId() === $user->getId())
        {
            $joueur = 1;
        } elseif ($game->getUser2()->getId() === $user->getId()) {
            $joueur = 2;
        } else {
            /// On a un problème... On pourrait rediriger vers une page d'erreur.
        }

        if ($joueur === 1) {
            $actions=$round->getUser2Action();
            $hands=$round->getUser1BoardCards();
            $adv=$round->getUser2BoardCards();
            $my_card=$cardRepository->find($carte);
            array_push($hands[$my_card->getName()],$carte);
            foreach ($actions['OFFRE'] as $card){
                if ($card != $carte){
                    $tcard=$cardRepository->find($card);
                    array_push($adv[$tcard->getName()],$card);
                }

            }
            $round->setUser2BoardCards($adv);
            $round->setUser1BoardCards($hands);
            $actions['OFFRE']= 'done';
            $round->setUser2Action($actions);
            $entityManager->flush();
            return $this->json(true);
        }

        if ($joueur === 2) {
            $actions=$round->getUser1Action();
            $hands=$round->getUser2BoardCards();
            $adv=$round->getUser1BoardCards();
            $my_card=$cardRepository->find($carte);
            array_push($hands[$my_card->getName()],$carte);
            foreach ($actions['OFFRE'] as $card){
                if ($card != $carte){
                    $tcard=$cardRepository->find($card);
                    array_push($adv[$tcard->getName()],$card);
                }

            }
            $round->setUser1BoardCards($adv);
            $round->setUser2BoardCards($hands);
            $actions['OFFRE']= 'done';
            $round->setUser1Action($actions);
            $entityManager->flush();
            return $this->json(true);
        }
    }

    /**
     * @Route("/update-echange/{game}", name="update_echange")
     */
    public function updateEchange(EntityManagerInterface $entityManager,
        Request $request,
        CardRepository $cardRepository,
        Game $game
    ): Response {
        $user = $this->getUser();
        $round = $game->getSets()[count($game->getSets())-1];
        $choix = $request->request->get('choix');
        $board1=$round->getUser1BoardCards();
        $board2= $round->getUser2BoardCards();

        if ($game->getUser1()->getId() === $user->getId())
        {
            $joueur = 1;
        } elseif ($game->getUser2()->getId() === $user->getId()) {
            $joueur = 2;
        } else {
            /// On a un problème... On pourrait rediriger vers une page d'erreur.
        }
        if ($joueur === 1) {
            $actions = $round->getUser2Action();
            if($choix == 'firstpair'){
                foreach ($actions['ECHANGE'][0] as $card){
                    $tcard=$cardRepository->find($card);
                    array_push($board1[$tcard->getName()],$card);
                }
                foreach ($actions['ECHANGE'][1] as $card){
                    $tcard=$cardRepository->find($card);
                    array_push($board2[$tcard->getName()],$card);
                }

            }else{
                foreach ($actions['ECHANGE'][0] as $card){
                    $tcard=$cardRepository->find($card);
                    array_push($board2[$tcard->getName()],$card);
                }
                foreach ($actions['ECHANGE'][1] as $card){
                    $tcard=$cardRepository->find($card);
                    array_push($board1[$tcard->getName()],$card);
                }
            }
            $round->setUser2BoardCards($board2);
            $round->setUser1BoardCards($board1);
            $actions['ECHANGE']= 'done';
            $round->setUser2Action($actions);
            $entityManager->flush();
        }

        if ($joueur === 2) {
            $actions = $round->getUser1Action();
            if($choix == 'firstpair'){
                foreach ($actions['ECHANGE'][0] as $card){
                    $tcard=$cardRepository->find($card);
                    array_push($board2[$tcard->getName()],$card);
                }
                foreach ($actions['ECHANGE'][1] as $card){
                    $tcard=$cardRepository->find($card);
                    array_push($board1[$tcard->getName()],$card);
                }

            }else{
                foreach ($actions['ECHANGE'][0] as $card){
                    $tcard=$cardRepository->find($card);
                    array_push($board1[$tcard->getName()],$card);
                }
                foreach ($actions['ECHANGE'][1] as $card){
                    $tcard=$cardRepository->find($card);
                    array_push($board2[$tcard->getName()],$card);
                }

            }
            $round->setUser2BoardCards($board2);
            $round->setUser1BoardCards($board1);
            $actions['ECHANGE']= 'done';
            $round->setUser1Action($actions);
            $entityManager->flush();
        }
        return $this->json(true);
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

