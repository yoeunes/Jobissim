<?php

namespace App\Form;

use App\Entity\Formation;
use App\Entity\CategoryFormation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Security\Core\Security;
use App\Repository\CategoryFormationRepository;
use Vich\UploaderBundle\Form\Type\VichFileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Mapping\Annotation\Uploadable;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class FormationType extends AbstractType
{
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;

    }
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('category', EntityType::class, [
                'class' => CategoryFormation::class,
                'label' => 'Catégorie',
                'query_builder' => function (CategoryFormationRepository $er) {
                    return $er->alpha();
                },
                ])
            ->add('nom', TextType::class, [
                'label' => 'Nom'
            ])
            ->add('lieu', TextType::class, [
                'label' => 'Lieu'
            ])
            ->add('date', DateType::class, [
                'label' => 'Date'
            ])
            ->add('duree', IntegerType::class, [
                'label' => 'Durée en mois',
                'required' => false
            ])
            ->add('prix', IntegerType::class, [
                'label' => 'Prix',
                'required' => false
            ])
            ->add('places', IntegerType::class, [
                'label' => 'Places'
            ])
            ->add('diplomes', TextType::class, [
                'label' => 'Diplômes',
                'required' => false
            ])
            ->add('objectif', TextareaType::class, [
                'label' => 'Objectif',
                'required' => false
            ])
            ->add('prerequis', TextareaType::class, [
                'label' => 'Prérequis',
                'required' => false
            ])
            ->add('contenu', TextareaType::class, [
                'label' => 'Contenu',
                'required' => false
            ])
            ->add('organisme', TextType::class, [
                'label' => 'Organisme'
            ])
            ->add('eligible', CheckboxType::class, array(
                'required' => false,
                'label' => 'Éligible',
                'value' => 1,
            ))
            ->add('imageFile', VichFileType::class, [
                'label' => 'Image (220x220)',
                'required' => false
            ])
            ->add('evaluationFile', VichFileType::class, [
                'label' => 'Évaluation',
                'required' => false
            ])
            ->add('submit', SubmitType::class, [
                'label' => "Ajouter"
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Formation::class,
        ]);
    }
}
