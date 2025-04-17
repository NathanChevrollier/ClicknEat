@extends('layouts.main')

@section('main')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="card mb-4">
        <div class="card-header">
            <h3 class="card-title">Mentions Légales</h3>
        </div>
        <div class="card-body">
            <h4>1. Éditeur du site</h4>
            <p>
                Le site Click'n Eat est édité par la société Click'n Eat SAS, au capital de 10 000 euros,
                immatriculée au Registre du Commerce et des Sociétés de Paris sous le numéro 123 456 789.
            </p>
            <p>
                <strong>Siège social :</strong> 123 Avenue de la Gastronomie, 75001 Paris, France<br>
                <strong>Téléphone :</strong> 01 23 45 67 89<br>
                <strong>Email :</strong> contact@clickneat.fr<br>
                <strong>Directeur de la publication :</strong> Jean Dupont, Président
            </p>

            <h4>2. Hébergement</h4>
            <p>
                Le site Click'n Eat est hébergé par la société OVH SAS, au capital de 10 174 560 euros,
                immatriculée au Registre du Commerce et des Sociétés de Lille Métropole sous le numéro 424 761 419.
            </p>
            <p>
                <strong>Siège social :</strong> 2 rue Kellermann, 59100 Roubaix, France<br>
                <strong>Téléphone :</strong> 09 72 10 10 07
            </p>

            <h4>3. Propriété intellectuelle</h4>
            <p>
                L'ensemble des éléments constituant le site Click'n Eat (textes, graphismes, logiciels, photographies, images, vidéos, sons, plans, logos, marques, etc.) ainsi que le site lui-même, sont protégés par les lois françaises et internationales relatives à la propriété intellectuelle.
            </p>
            <p>
                Ces éléments sont la propriété exclusive de Click'n Eat SAS. Toute reproduction, représentation, utilisation, adaptation, modification, incorporation, traduction, commercialisation, partielle ou intégrale de ces éléments, sans l'autorisation écrite préalable de Click'n Eat SAS, est strictement interdite et constitue un délit de contrefaçon sanctionné par les articles L.335-2 et suivants du Code de la propriété intellectuelle.
            </p>

            <h4>4. Données personnelles</h4>
            <p>
                Les informations recueillies sur ce site font l'objet d'un traitement informatique destiné à Click'n Eat SAS pour la gestion de sa clientèle et la prospection commerciale.
            </p>
            <p>
                Conformément à la loi « Informatique et Libertés » du 6 janvier 1978 modifiée, et au Règlement Général sur la Protection des Données (RGPD), vous disposez d'un droit d'accès, de rectification, d'effacement, de limitation, de portabilité et d'opposition aux données personnelles vous concernant.
            </p>
            <p>
                Pour exercer ces droits ou pour toute question sur le traitement de vos données, vous pouvez contacter notre Délégué à la Protection des Données à l'adresse email suivante : dpo@clickneat.fr ou par courrier à l'adresse postale mentionnée ci-dessus.
            </p>
            <p>
                Pour plus d'informations sur vos droits, consultez notre <a href="{{ route('privacy.policy') }}">Politique de confidentialité</a>.
            </p>

            <h4>5. Cookies</h4>
            <p>
                Le site Click'n Eat utilise des cookies pour améliorer l'expérience utilisateur et proposer des contenus personnalisés. En naviguant sur notre site, vous acceptez l'utilisation de cookies conformément à notre <a href="{{ route('privacy.policy') }}">Politique de confidentialité</a>.
            </p>

            <h4>6. Limitation de responsabilité</h4>
            <p>
                Click'n Eat SAS s'efforce d'assurer au mieux de ses possibilités l'exactitude et la mise à jour des informations diffusées sur son site, dont elle se réserve le droit de corriger, à tout moment et sans préavis, le contenu.
            </p>
            <p>
                Toutefois, Click'n Eat SAS ne peut garantir l'exactitude, la précision ou l'exhaustivité des informations mises à disposition sur son site. En conséquence, Click'n Eat SAS décline toute responsabilité :
            </p>
            <ul>
                <li>pour toute imprécision, inexactitude ou omission portant sur des informations disponibles sur le site ;</li>
                <li>pour tous dommages résultant d'une intrusion frauduleuse d'un tiers ayant entraîné une modification des informations mises à disposition sur le site ;</li>
                <li>et plus généralement, pour tous dommages, directs ou indirects, qu'elles qu'en soient les causes, origines, natures ou conséquences, provenant de l'accès de quiconque au site ou de l'impossibilité d'y accéder.</li>
            </ul>

            <h4>7. Liens hypertextes</h4>
            <p>
                Le site Click'n Eat peut contenir des liens hypertextes vers d'autres sites internet. Click'n Eat SAS n'exerce aucun contrôle sur ces sites et décline toute responsabilité quant à leur contenu.
            </p>

            <h4>8. Droit applicable et juridiction compétente</h4>
            <p>
                Les présentes mentions légales sont régies par le droit français. En cas de litige, les tribunaux français seront seuls compétents.
            </p>

            <h4>9. Contact</h4>
            <p>
                Pour toute question relative aux présentes mentions légales, vous pouvez nous contacter à l'adresse email suivante : legal@clickneat.fr
            </p>

            <p class="text-muted mt-4">
                Dernière mise à jour : 10 avril 2025
            </p>
        </div>
    </div>
</div>
@endsection
