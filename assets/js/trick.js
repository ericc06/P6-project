// La fonction qui ajoute un lien de suppression d'un media
function addDeleteLink($prototype) { // Création du lien
    var $deleteLink = $('<a href="#" class="btn btn-danger">'
        + translations['remove_media_trans'] + '</a>');
    // Ajout du lien
    $prototype.append($deleteLink);

    // Ajout du listener sur le clic du lien pour effectivement supprimer le media
    $deleteLink.click(function(e) {
        $prototype.remove();

        e.preventDefault(); // évite qu'un # apparaisse dans l'URL
        return false;
    });
}

$(document).ready(function () {
    // On page load, we first hide the form elements not useful for medias update:
    // For images we hide the fileUrl field.
    // For videos we hide the file upload field.
    $("input[id$='_fileType'][value='0']").parentsUntil("fieldset.form-group")
        .find("input[id$='_fileUrl']").parent("div").css("visibility", "hidden")
        .css("height", "0").css("margin", "0");

    $("input[id$='_fileType'][value='1']").parentsUntil("fieldset.form-group")
        .find("input[id$='_file']").parent("div").css("visibility", "hidden")
        .css("height", "0").css("margin", "0");
    
    $("input[id$='_fileType'][value='1']").parentsUntil("fieldset.form-group")
        .find("input[id$='_defaultCover']").parent("div")
        .css("visibility", "hidden").css("height", "0").css("margin", "0")
        .parent("div").css("margin-bottom", "0");

    // On récupère la balise <div> en question qui contient l'attribut
    // "data-prototype" qui nous intéresse.
    var $container = $("div#trick_medias");
    // On définit un compteur unique pour nommer les champs qu'on va ajouter dynamiquement
    var index = $container.find(":input").length;

    // La fonction qui ajoute un formulaire MediaType
    function addMedia($container, $mediaType, $fieldsetLabel, $showRemoveButton = true) {
        $(".trick_edit_form_container").find(".well").children()
            .not(".edit_form_buttons_div").css("display", "block");
        $(".trick_edit_form_container").find(".well")
            .find(".add_media_buttons_div").css("display", "flex");
        // Dans le contenu de l'attribut « data-prototype », on remplace :
        // - le texte "__name__label__" qu'il contient par le label du champ
        // - le texte "__name__" qu'il contient par le numéro du champ
        var template = $container.attr("data-prototype")
            .replace(/__name__label__/g, $fieldsetLabel)
            .replace(/__name__/g, index);
        // On crée un objet jquery qui contient ce template
        var $prototype = $(template);
        // On ajoute au prototype un bouton pour pouvoir supprimer le media
        // si $showRemoveButton n'a pas été passé avec la valeur false
        if ($showRemoveButton) {
            addDeleteLink($prototype);
        }
        // On ajoute le prototype modifié à la fin de la balise <div>
        $container.append($prototype.css("display", "block")
            .css("border", "3px dashed #bbb"));
        
        switch ($mediaType) {
            case "image":
                // On montre le champ d'upload de l'image et on cache celui
                // de saisie de l'URL de la video
                $("input#trick_medias_" + index + "_file").parent().closest("div").show();
                let radioButton = $("input#trick_medias_" + index + "_defaultCover");
                let radioName = radioButton.attr("name");
                radioButton.attr("original-name", radioName).on("click", function (e) {
                    let name = $(this).attr("original-name");
                    $("form input:radio").attr("name", name);
                    // We need this, else the radio button is not always checked.
                    $(this).prop("checked", true);
                });
                $("input#trick_medias_" + index + "_fileUrl").parent().closest("div").hide();
                // On initialise le champ hidden "fileType" à 0 (valeur pour une image)
                $("input#trick_medias_" + index + "_fileType").val(0);

                // We set the "required" attribute to all required elements 
                $("input#trick_medias_" + index + "_file").attr("required", "required");
                $("input#trick_medias_" + index + "_title").attr("required", "required");
                $("input#trick_medias_" + index + "_alt").attr("required", "required");

                break;
            case "video":
                // On cache le champ d'upload de l'image et on montre celui de saisie
                // de l'URL de la video
                $("input#trick_medias_" + index + "_file").parent().closest("div").hide();
                $("input#trick_medias_" + index + "_defaultCover").parent()
                    .closest("div.form-group").hide();
                $("input#trick_medias_" + index + "_fileUrl").parent().closest("div").show();
                // On initialise le champ hidden "fileType" à 1 (valeur pour une video)
                $("input#trick_medias_" + index + "_fileType").val(1);

                // We set the "required" attribute to all required elements 
                $("input#trick_medias_" + index + "_fileUrl").attr("required", "required");
                $("input#trick_medias_" + index + "_title").attr("required", "required");
                $("input#trick_medias_" + index + "_alt").attr("required", "required");

                break;
            default:
                break;
        }
        // Enfin, on incrémente le compteur pour que le prochain ajout se fasse avec un autre numéro
        index++;
    }

    // On ajoute un nouveau champ à chaque clic sur le lien d'ajout.
    $("#add_image").click(function (e) {
        addMedia($container, "image", translations["added_image_trans"]);
        e.preventDefault(); // évite qu'un # apparaisse dans l'URL
        return false;
    });
    $("#add_video").click(function (e) {
        addMedia($container, "video", translations["added_video_trans"]);
        e.preventDefault(); // évite qu'un # apparaisse dans l'URL
        return false;
    });

    // On ajoute un premier champ automatiquement s'il n'en existe pas déjà un
    // (cas d'une nouvelle figure par exemple).
    if (index === 0) {
        addMedia($container, "image", translations["at_least_one_image_trans"], false);
    } else {
        // S'il existe déjà des medias, on ajoute un lien de suppression
        // pour chacun d'entre eux. C'est le cas si le formulaire est rechargé
        // après une erreur de validation.
        // Par contre il ne faut pas ces boutons pour les médias dejà existants
        // en base (cas de l'édition d'un trick).
        $container.children("fieldset").each(function () {
            let fieldsetNumber = $(this).children("legend").html();
            if (fieldsetNumber !== "0") {
                // Existing medias (trick modification) have a "#trick_medias_X_id"
                // hidden input with a numeric value (media id).
                // This value is not present for non existing medias.
                if(! $("#trick_medias_" + fieldsetNumber + "_id").val()) {
                    addDeleteLink($(this));
                }
                if ($(this).children("div#trick_medias_" + fieldsetNumber)
                    .children("input#trick_medias_"+fieldsetNumber+"_fileType").val() === 0) {
                    $(this).children("legend").html(translations["added_image_trans"]);
                } else {
                    $(this).children("legend").html(translations["added_video_trans"]);
                }
            } else {
                $(this).children("legend").html(translations["at_least_one_image_trans"]);
            }
        });
    }

    // We memorize the original name of each radio button and we store it
    // in the correponding radio button. We"ll need it because radio buttons
    // names are changed (all set with the same value) on each radio button click.
    $("form input:radio").each(function (e) {
        let radioName = $(this).attr("name");
        $(this).attr("original-name", radioName);
    });

    // When a radio button is checked, we give his original name to him
    // and to all others so that they all work together:
    // checking one unchecks all others.
    $("form input:radio").on("click", function (e) {
        let radioName = $(this).attr("original-name");
        $("form input:radio").attr("name", radioName);
    });

    // When the form submit button is clicked, we disable it
    // to avoid double submission.
    $("form").submit(function () {
        $("input[type=submit]").attr("disabled", true);
        return true;
    });
});