<?php

namespace App\Controller\Admin;

use App\Entity\Stats;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class StatsCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Stats::class;
    }


    public function configureFields(string $pageName): iterable
    {
        return [
            IntegerField::new('id'),
            IntegerField::new('victoires'),
            IntegerField::new('defaites'),
            IntegerField::new('pieces'),
            IntegerField::new('temps_jeu'),
            IntegerField::new('parties_terminees'),
            IntegerField::new('parties_jouees'),
            IntegerField::new('parties_en_cours'),

        ];
    }

}
