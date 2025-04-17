@extends('layouts.main')

@section('main')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="card mb-4">
        <div class="card-header">
            <h3 class="card-title">Conditions Générales d'Utilisation</h3>
        </div>
        <div class="card-body">
            <h4>1. Objet</h4>
            <p>
                Les présentes Conditions Générales d'Utilisation (ci-après « CGU ») ont pour objet de définir les modalités et conditions d'utilisation des services proposés sur le site Click'n Eat (ci-après « le Service »), ainsi que de définir les droits et obligations des parties dans ce cadre.
            </p>
            <p>
                Elles sont accessibles et imprimables à tout moment par un lien direct en bas de page du site.
            </p>
            <p>
                Elles peuvent être complétées, le cas échéant, par des conditions d'utilisation particulières à certains services. En cas de contradiction, les conditions particulières prévalent sur ces conditions générales.
            </p>

            <h4>2. Exploitant des services</h4>
            <p>
                Le Service est exploité par la société Click'n Eat SAS, au capital de 10 000 euros, immatriculée au Registre du Commerce et des Sociétés de Paris sous le numéro 123 456 789, dont le siège social est situé 123 Avenue de la Gastronomie, 75001 Paris (ci-après « Click'n Eat »).
            </p>
            <p>
                Click'n Eat peut être contactée aux coordonnées suivantes :
                <br>Adresse postale : 123 Avenue de la Gastronomie, 75001 Paris
                <br>Adresse électronique : contact@clickneat.fr
                <br>Téléphone : 01 23 45 67 89
            </p>

            <h4>3. Accès au site et aux services</h4>
            <p>
                Le Service est accessible, sous réserve des restrictions prévues sur le site :
            </p>
            <ul>
                <li>à toute personne physique disposant de la pleine capacité juridique pour s'engager au titre des présentes conditions générales. La personne physique qui ne dispose pas de la pleine capacité juridique ne peut accéder au Site et aux Services qu'avec l'accord de son représentant légal ;</li>
                <li>à toute personne morale agissant par l'intermédiaire d'une personne physique disposant de la capacité juridique pour contracter au nom et pour le compte de la personne morale.</li>
            </ul>

            <h4>4. Inscription sur le site</h4>
            <p>
                L'utilisation des Services nécessite que l'Utilisateur s'inscrive sur le site, en remplissant le formulaire prévu à cet effet. L'Utilisateur doit fournir l'ensemble des informations marquées comme obligatoires. Toute inscription incomplète ne sera pas validée.
            </p>
            <p>
                L'inscription entraîne automatiquement l'ouverture d'un compte au nom de l'Utilisateur (ci-après : le « Compte »), lui donnant accès à un espace personnel (ci-après : l'« Espace Personnel ») qui lui permet de gérer son utilisation des Services.
            </p>
            <p>
                L'Utilisateur garantit que toutes les informations qu'il donne dans le formulaire d'inscription sont exactes, à jour et sincères et ne sont entachées d'aucun caractère trompeur.
            </p>
            <p>
                Il s'engage à mettre à jour ces informations dans son Espace Personnel en cas de modifications, afin qu'elles correspondent toujours aux critères susvisés.
            </p>

            <h4>5. Description des services</h4>
            <p>
                Click'n Eat met à disposition des Utilisateurs une plateforme en ligne dédiée à la commande de repas auprès de restaurants partenaires. Les Services proposés incluent notamment :
            </p>
            <ul>
                <li>La consultation des menus et plats proposés par les restaurants partenaires</li>
                <li>La commande en ligne de repas</li>
                <li>Le paiement en ligne des commandes</li>
                <li>Le suivi des commandes</li>
                <li>Pour les restaurateurs, la gestion de leur établissement, menus et commandes</li>
            </ul>

            <h4>6. Services gratuits et payants</h4>
            <p>
                L'inscription sur le site est gratuite.
            </p>
            <p>
                La commande de repas est payante, selon les prix affichés sur le site pour chaque plat ou menu. Les prix sont indiqués en euros et comprennent les taxes applicables.
            </p>
            <p>
                Des frais de livraison peuvent s'appliquer en fonction de la distance et sont indiqués avant la validation de la commande.
            </p>

            <h4>7. Responsabilités et garanties</h4>
            <p>
                Click'n Eat s'engage à fournir ses Services avec diligence et selon les règles de l'art, étant précisé qu'il pèse sur elle une obligation de moyens, à l'exclusion de toute obligation de résultat, ce que les Utilisateurs reconnaissent et acceptent expressément.
            </p>
            <p>
                Click'n Eat n'a pas connaissance des contenus mis en ligne par les Utilisateurs dans le cadre des Services, sur lesquels elle n'effectue aucune modération, sélection, vérification ou contrôle d'aucune sorte et à l'égard desquels elle n'intervient qu'en tant que prestataire d'hébergement.
            </p>
            <p>
                Click'n Eat ne peut être tenue responsable des contenus dont les auteurs sont des tiers, toute réclamation éventuelle devant être dirigée en premier lieu vers l'auteur des contenus en question.
            </p>

            <h4>8. Propriété intellectuelle</h4>
            <p>
                Les systèmes, logiciels, structures, infrastructures, bases de données et contenus de toute nature (textes, images, visuels, musiques, logos, marques, base de données, etc.) exploités par Click'n Eat au sein du site sont protégés par tous droits de propriété intellectuelle ou droits des producteurs de bases de données en vigueur.
            </p>
            <p>
                Tous désassemblages, décompilations, décryptages, extractions, réutilisations, copies et plus généralement, tous actes de reproduction, représentation, diffusion et utilisation de l'un quelconque de ces éléments, en tout ou partie, sans l'autorisation de Click'n Eat sont strictement interdits et pourront faire l'objet de poursuites judiciaires.
            </p>

            <h4>9. Données à caractère personnel</h4>
            <p>
                Click'n Eat pratique une politique de protection des données personnelles dont les caractéristiques sont explicitées dans le document intitulé « <a href="{{ route('privacy.policy') }}">Politique de confidentialité</a> », accessible sur le site.
            </p>

            <h4>10. Sanctions</h4>
            <p>
                En cas de manquement à l'une quelconque des dispositions des présentes conditions générales ou plus généralement, d'infraction aux lois et règlements en vigueur par un Utilisateur, Click'n Eat se réserve le droit de prendre toute mesure appropriée et notamment de :
            </p>
            <ul>
                <li>suspendre ou résilier l'accès aux Services de l'Utilisateur, auteur du manquement ou de l'infraction, ou y ayant participé,</li>
                <li>supprimer tout contenu mis en ligne sur le site,</li>
                <li>publier sur le site tout message d'information que Click'n Eat jugera utile,</li>
                <li>avertir toute autorité concernée,</li>
                <li>engager toute action judiciaire.</li>
            </ul>

            <h4>11. Modifications</h4>
            <p>
                Click'n Eat se réserve la faculté de modifier à tout moment les présentes conditions générales d'utilisation.
            </p>
            <p>
                L'Utilisateur sera informé de ces modifications par tout moyen utile.
            </p>
            <p>
                L'Utilisateur qui n'accepte pas les conditions générales modifiées doit se désinscrire des Services.
            </p>
            <p>
                Les conditions générales modifiées s'appliquent immédiatement aux Utilisateurs qui s'inscrivent aux Services après cette modification.
            </p>

            <h4>12. Loi applicable et juridiction</h4>
            <p>
                Les présentes conditions générales sont régies par la loi française.
            </p>
            <p>
                En cas de contestation sur la validité, l'interprétation et/ou l'exécution des présentes conditions générales, les parties conviennent que les tribunaux de Paris seront exclusivement compétents pour en juger, sauf règles de procédure impératives contraires.
            </p>

            <p class="text-muted mt-4">
                Dernière mise à jour : 10 avril 2025
            </p>
        </div>
    </div>
</div>
@endsection
