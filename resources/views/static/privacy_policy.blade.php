@extends('layouts.main')

@section('main')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="card mb-4">
        <div class="card-header">
            <h3 class="card-title">Politique de Confidentialité</h3>
        </div>
        <div class="card-body">
            <p class="mb-4">
                La présente politique de confidentialité définit et vous informe de la manière dont Click'n Eat utilise et protège les informations que vous nous transmettez lorsque vous utilisez notre site.
            </p>

            <h4>1. Identité du responsable du traitement</h4>
            <p>
                Le responsable du traitement des données à caractère personnel est :
                <br>Click'n Eat SAS
                <br>123 Avenue de la Gastronomie, 75001 Paris
                <br>Email : dpo@clickneat.fr
            </p>

            <h4>2. Données collectées</h4>
            <p>
                Dans le cadre de votre utilisation du site Click'n Eat, nous sommes amenés à collecter et traiter les données suivantes :
            </p>
            <ul>
                <li><strong>Données d'identification</strong> : nom, prénom, adresse email, numéro de téléphone</li>
                <li><strong>Données de livraison</strong> : adresse postale, instructions de livraison</li>
                <li><strong>Données de facturation</strong> : coordonnées bancaires (uniquement lors du processus de paiement)</li>
                <li><strong>Données de connexion</strong> : adresse IP, date et heure de connexion</li>
                <li><strong>Données de navigation</strong> : cookies, historique de navigation</li>
                <li><strong>Données de commande</strong> : historique des commandes, préférences alimentaires</li>
            </ul>

            <h4>3. Finalités du traitement</h4>
            <p>
                Les données que nous collectons auprès de vous sont utilisées pour les finalités suivantes :
            </p>
            <ul>
                <li>Création et gestion de votre compte utilisateur</li>
                <li>Traitement et suivi de vos commandes</li>
                <li>Facturation et paiement</li>
                <li>Livraison de vos commandes</li>
                <li>Service client et assistance</li>
                <li>Amélioration de nos services et de votre expérience utilisateur</li>
                <li>Communication sur nos offres et promotions (avec votre consentement)</li>
                <li>Respect de nos obligations légales et réglementaires</li>
                <li>Prévention et détection des fraudes</li>
            </ul>

            <h4>4. Base légale du traitement</h4>
            <p>
                Nous traitons vos données personnelles sur les bases légales suivantes :
            </p>
            <ul>
                <li><strong>L'exécution du contrat</strong> que nous avons conclu avec vous (traitement de vos commandes, livraison, service client)</li>
                <li><strong>Notre intérêt légitime</strong> à développer et promouvoir nos activités (amélioration de nos services, prévention des fraudes)</li>
                <li><strong>Votre consentement</strong> pour l'envoi de communications marketing</li>
                <li><strong>Le respect de nos obligations légales</strong> (conservation des factures, etc.)</li>
            </ul>

            <h4>5. Destinataires des données</h4>
            <p>
                Les données que nous collectons peuvent être transmises aux catégories de destinataires suivantes :
            </p>
            <ul>
                <li>Les restaurants partenaires (uniquement pour les données nécessaires à la préparation et à la livraison de votre commande)</li>
                <li>Nos prestataires de services de paiement (uniquement pour les données nécessaires au traitement du paiement)</li>
                <li>Nos prestataires de services de livraison (uniquement pour les données nécessaires à la livraison)</li>
                <li>Nos prestataires techniques (hébergement, maintenance)</li>
                <li>Les autorités administratives ou judiciaires lorsque la loi l'exige</li>
            </ul>

            <h4>6. Durée de conservation des données</h4>
            <p>
                Nous conservons vos données personnelles pour la durée nécessaire à l'accomplissement des finalités pour lesquelles elles ont été collectées, augmentée des délais légaux de prescription :
            </p>
            <ul>
                <li>Données de compte : pendant toute la durée de votre inscription au service, puis 3 ans à compter de votre dernière activité</li>
                <li>Données de commande : 10 ans à compter de la commande (obligations comptables et fiscales)</li>
                <li>Données de paiement : supprimées immédiatement après la transaction (seules les 4 derniers chiffres de votre carte bancaire peuvent être conservés à des fins de preuve)</li>
                <li>Données de connexion et de navigation : 13 mois maximum</li>
            </ul>

            <h4>7. Vos droits</h4>
            <p>
                Conformément à la réglementation applicable en matière de protection des données personnelles, vous disposez des droits suivants :
            </p>
            <ul>
                <li><strong>Droit d'accès</strong> : vous pouvez obtenir des informations concernant le traitement de vos données personnelles ainsi qu'une copie de ces données</li>
                <li><strong>Droit de rectification</strong> : si vous estimez que vos données personnelles sont inexactes ou incomplètes, vous pouvez exiger que ces données soient modifiées en conséquence</li>
                <li><strong>Droit à l'effacement</strong> : vous pouvez exiger l'effacement de vos données personnelles dans les limites prévues par la réglementation</li>
                <li><strong>Droit à la limitation du traitement</strong> : vous pouvez demander la limitation du traitement de vos données personnelles</li>
                <li><strong>Droit d'opposition</strong> : vous pouvez vous opposer au traitement de vos données personnelles pour des motifs liés à votre situation particulière</li>
                <li><strong>Droit à la portabilité des données</strong> : vous avez le droit de recevoir vos données personnelles dans un format structuré, couramment utilisé et lisible par machine, et de les transmettre à un autre responsable du traitement</li>
                <li><strong>Droit de retirer votre consentement</strong> à tout moment, lorsque le traitement est fondé sur votre consentement</li>
                <li><strong>Droit de définir des directives</strong> relatives au sort de vos données après votre décès</li>
            </ul>
            <p>
                Pour exercer ces droits, vous pouvez nous contacter à l'adresse suivante : dpo@clickneat.fr ou par courrier à l'adresse : Click'n Eat SAS, 123 Avenue de la Gastronomie, 75001 Paris.
            </p>
            <p>
                Vous disposez également du droit d'introduire une réclamation auprès de la Commission Nationale de l'Informatique et des Libertés (CNIL).
            </p>

            <h4>8. Transfert de données hors de l'Union Européenne</h4>
            <p>
                Click'n Eat s'engage à ne pas transférer vos données personnelles en dehors de l'Union Européenne. Si, pour des raisons techniques ou opérationnelles, un tel transfert devait avoir lieu, nous mettrions en place toutes les garanties appropriées conformément à la réglementation applicable pour assurer un niveau de protection adéquat de vos données.
            </p>

            <h4>9. Sécurité des données</h4>
            <p>
                Nous mettons en œuvre des mesures techniques et organisationnelles appropriées pour protéger vos données personnelles contre la destruction accidentelle ou illicite, la perte accidentelle, l'altération, la diffusion ou l'accès non autorisés.
            </p>
            <p>
                Ces mesures incluent notamment :
            </p>
            <ul>
                <li>Le chiffrement des données sensibles</li>
                <li>Des procédures de sauvegarde régulières</li>
                <li>Des contrôles d'accès stricts</li>
                <li>Des audits de sécurité</li>
            </ul>

            <h4>10. Cookies</h4>
            <p>
                Notre site utilise des cookies pour améliorer votre expérience de navigation, personnaliser le contenu et les publicités, fournir des fonctionnalités de réseaux sociaux et analyser notre trafic.
            </p>
            <p>
                Vous pouvez contrôler l'utilisation des cookies en paramétrant votre navigateur. Toutefois, si vous désactivez certains cookies, il est possible que vous ne puissiez pas utiliser toutes les fonctionnalités de notre site.
            </p>
            <p>
                Pour plus d'informations sur les cookies que nous utilisons, veuillez consulter notre politique en matière de cookies accessible sur notre site.
            </p>

            <h4>11. Modification de la politique de confidentialité</h4>
            <p>
                Nous nous réservons le droit de modifier la présente politique de confidentialité à tout moment. Toute modification sera publiée sur cette page et, si les modifications sont significatives, nous vous fournirons une notification plus visible.
            </p>
            <p>
                Nous vous encourageons à consulter régulièrement cette page pour prendre connaissance des éventuelles modifications.
            </p>

            <p class="text-muted mt-4">
                Dernière mise à jour : 10 avril 2025
            </p>
        </div>
    </div>
</div>
@endsection
