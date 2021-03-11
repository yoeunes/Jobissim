<?php

namespace App\Controller\Admin;

use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;

class UserCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return User::class;
    }
    
    public function configureFields(string $pageName): iterable
    {
        return [
            EmailField::new('email'),
            TextField::new('firstName')->hideOnIndex(),
            TextField::new('lastName'),
            ChoiceField::new('roles')->setChoices([
                'ROLE_USER' => 'ROLE_USER',
                'ROLE_SIMPLE' => 'ROLE_SIMPLE',
                'ROLE_FORMATEUR' => 'ROLE_FORMATEUR',
                'ROLE_FORMATEUR_VALIDE' => 'ROLE_FORMATEUR_VALIDE',
                'ROLE_EMPLOYEUR' => 'ROLE_EMPLOYEUR',
                'ROLE_EMPLOYEUR_1' => 'ROLE_EMPLOYEUR_1',
                'ROLE_EMPLOYEUR_5' => 'ROLE_EMPLOYEUR_5',
                'ROLE_EMPLOYEUR_10' => 'ROLE_EMPLOYEUR_10',
                'ROLE_EMPLOYEUR_ILLIMITE' => 'ROLE_EMPLOYEUR_ILLIMITE',
                'ROLE_ADMIN' => 'ROLE_ADMIN',
                'ROLE_STATS' => 'ROLE_STATS',
                'ROLE_EVALUATION' => 'ROLE_EVALUATION',
                'ROLE_CVTHEQUE' => 'ROLE_CVTHEQUE',
                'ROLE_VISIO' => 'ROLE_VISIO',
                'ROLE_EMPFORM' => 'ROLE_EMPFORM',
            ])->allowMultipleChoices(),
            ChoiceField::new('civilite')->setChoices([
                'Mr' => 'Mr',
                'Mme' => 'Mme',
            ])->hideOnIndex(),
            BooleanField::new('cvonline')
        ];
    }
    
}
