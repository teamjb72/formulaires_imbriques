<?php

namespace AppBundle\Form;

use AppBundle\Entity\Departement;
use AppBundle\Entity\Region;
use AppBundle\Entity\Ville;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormEvents;


class MedecinType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add( 'name' )
            ->add( 'region', EntityType::class, [
                'class' => 'AppBundle\Entity\Region',
                'placeholder' => 'Sélectionnez votre région',
                'mapped' => false,
                'required' => false
            ] );

        $builder->get( 'region' )->addEventListener(
            FormEvents::POST_SUBMIT,
            function (FormEvent $event) {
                $form = $event->getForm();
                $region = $event->getForm()->getData();
                $this->addDepartementField( $form->getParent(), $region );

            }
        );

        $builder->addEventListener(
            FormEvents::POST_SET_DATA,
            function (FormEvent $event){
                $data = $event->getData();
                $ville= $data->getVille();
                $form = $event->getForm();
                /* @var $ville Ville */
                if ($ville) {
                   $departement = $ville->getDepartement();
                   $region = $departement->getRegion();
                   $this->addDepartementField($form, $region);
                   $this->addVilleField($form, $departement);
                   $form->get('region')->setData($region);
                    $form->get('departement')->setData($departement);

                }else{
                    $this->addDepartementField($form, null);
                    $this->addVilleField($form, null);
                }
            }
        );
    }

    /** Rajoute un champ ville au formulaire
     * @param FormInterface $form
     * @param Departement $departement
     */
    private function addVilleField(FormInterface $form, Departement $departement = null)
    {
        $form->add(
            'ville',
            EntityType::class,

            [
                'class' => 'AppBundle\Entity\Ville',
                'placeholder' => $departement ? 'Sélectionnez votre ville' : 'Selectionner dabord votre département',
                'choices' => $departement? $departement->getVilles() : []

            ]
        );


    }

    /** Rajoute un champ departement au formulaire
     * @param FormInterface $form
     * @param Region $region
     */
    private function addDepartementField(FormInterface $form, Region $region = null)
    {
        $builder = $form->getConfig()->getFormFactory()->createNamedBuilder(
            'departement',
            EntityType::class,
            null,
            [
                'class' => 'AppBundle\Entity\Departement',
                'placeholder' => $region ? 'Sélectionnez votre département' : 'Selectionner d abord une region',
                'mapped' => false,
                'required' => false,
                'auto_initialize' => false,
                'empty_data' => null,
                'choices' => $region ? $region->getDepartements(): []

            ]
        );
        $builder->addEventListener(
            FormEvents::POST_SUBMIT,
            function (FormEvent $event) {
                $form = $event->getForm()->getParent();

                $dep = $event->getForm()->getData();

                if ($dep instanceof Departement) {


                    //  dump( $event->getData() );
                    $this->addVilleField( $form, $dep );
                }


            }
        );

        $form->add( $builder->getForm() );
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults( array(
            'data_class' => 'AppBundle\Entity\Medecin'
        ) );
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_medecin';
    }


}
