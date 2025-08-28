$(".change-language").on("click", function() {
    let getText = $("#get-confirm-and-cancel-button-text");
    Swal.fire({
        title: getText.data("sure"),
        text: $("#change-language-message").data("text"),
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        cancelButtonText: getText.data("cancel"),
        confirmButtonText: getText.data("confirm"),
        reverseButtons: true
    }).then(result => {
        if (result.value) {
            $.ajaxSetup({
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="_token"]').attr("content")
                }
            });
            $.ajax({
                type: "POST",
                url: $(this).data("action"),
                data: {
                    language_code: $(this).data("language-code")
                },
                success: function(data) {
                    toastMagic.success(data.message);
                    location.reload();
                }
            });
        }
    });
});

$("#formUrlChange").on("click", function() {
    let action = $(this).data("action");
    $("#form-data").attr("action", action);
});

function callDemo() {
    $(".call-demo").on("click", function() {
        toastMagic.info($("#call-demo-message").data("text"));
    });
}
callDemo();

$(".toggle-switch-dynamic-image").on("click", function(event) {
    event.preventDefault();
    const modalId = $(this).data("modal-id");
    const toggleId = $(this).data("toggle-id");
    const onImage = $(this).data("on-image");
    const offImage = $(this).data("off-image");
    const onTitle = $(this).data("on-title");
    const offTitle = $(this).data("off-title");
    const onMessage = $(this).data("on-message");
    const offMessage = $(this).data("off-message");
    toggleModal(
        modalId,
        toggleId,
        onImage,
        offImage,
        onTitle,
        offTitle,
        onMessage,
        offMessage
    );
});

$(document).on("click", ".toggle-switch-message", function(event) {
    event.preventDefault();
    let rootPath = $("#get-root-path-for-toggle-modal-image").data("path");
    const modalId = $(this).data("modal-id");
    const toggleId = $(this).data("toggle-id");
    const onImage = rootPath + "/" + $(this).data("on-image");
    const offImage = rootPath + "/" + $(this).data("off-image");
    const onTitle = $(this).data("on-title");
    const offTitle = $(this).data("off-title");
    const onMessage = $(this).data("on-message");
    const offMessage = $(this).data("off-message");
    const onBtnText = $(this).data("on-button-text");
    const offBtnText = $(this).data("off-button-text");
    const verification = $(this).data("verification");

    if (verification && verification === "firebase-auth") {
        try {
            if (checkFirebaseAuthVerification()) {
                toggleModal(
                    modalId,
                    toggleId,
                    onImage,
                    offImage,
                    onTitle,
                    offTitle,
                    onMessage,
                    offMessage
                );
            }
        } catch (e) {
            console.log(e);
        }
    } else {
        toggleModal(
            modalId,
            toggleId,
            onImage,
            offImage,
            onTitle,
            offTitle,
            onMessage,
            offMessage,
            onBtnText,
            offBtnText
        );
    }
});

function toggleModal(
    modalId,
    toggleId,
    onImage = null,
    offImage = null,
    onTitle,
    offTitle,
    onMessage,
    offMessage,
    onBtnText,
    offBtnText
) {
    if ($("#" + toggleId).is(":checked")) {
        $("#" + modalId + "-title")
            .empty()
            .append(onTitle);
        $("#" + modalId + "-message")
            .empty()
            .append(onMessage);
        $("." + modalId + "-button-text")
            .empty()
            .append(onBtnText);
        $("#" + modalId + "-image").attr("src", onImage);
        $("#" + modalId + "-ok-button").attr("toggle-ok-button", toggleId);
    } else {
        $("#" + modalId + "-title")
            .empty()
            .append(offTitle);
        $("#" + modalId + "-message")
            .empty()
            .append(offMessage);
        $("." + modalId + "-button-text")
            .empty()
            .append(offBtnText);
        $("#" + modalId + "-image").attr("src", offImage);
        $("#" + modalId + "-ok-button").attr("toggle-ok-button", toggleId);
    }
    $("#" + modalId).modal("show");
}

$("#toggle-modal-ok-button").on("click", function() {
    const toggleIdName = $(this).attr("toggle-ok-button");
    const toggleId = $("#" + $(this).attr("toggle-ok-button"));
    if (toggleId.is(":checked")) {
        toggleId.prop("checked", false);
    } else {
        toggleId.prop("checked", true);
    }
    $("#toggle-modal").modal("hide");
    if (toggleIdName === "email-verification") {
        if (
            $("#email-verification").is(":checked") &&
            $("#otp-verification").is(":checked")
        ) {
            $("#otp-verification").removeAttr("checked");
            toastMagic.info(
                $("#get-email-and-otp-verification-info-message").data("info")
            );
        }
    }
    if (
        toggleIdName === "otp-verification" &&
        $("#get-application-environment-mode").data("value") !== "demo"
    ) {
        if (
            $("#otp-verification").is(":checked") &&
            $("#email-verification").is(":checked")
        ) {
            $("#email-verification").removeAttr("checked");
            toastMagic.info(
                $("#get-email-and-otp-verification-info-message").data("info")
            );
        }
    } else {
        callDemo();
    }
});

function checkAlternativeCheckbox(toggleIdName) {
    if (toggleIdName === "storage-connection-s3") {
        let storageConnectionS3 = $("#storage-connection-s3");
        let storageConnectionLocal = $("#storage-connection-local");
        if (storageConnectionS3.is(":checked")) {
            storageConnectionLocal.removeAttr("checked");
        } else {
            storageConnectionLocal.prop("checked", true);
        }
    }

    if (toggleIdName === "storage-connection-local") {
        let storageConnectionS3 = $("#storage-connection-s3");
        let storageConnectionLocal = $("#storage-connection-local");
        if (storageConnectionLocal.is(":checked")) {
            storageConnectionS3.removeAttr("checked");
        } else {
            storageConnectionS3.prop("checked", true);
        }
    }
}

$("#toggle-status-modal-ok-button").on("click", function() {
    const toggleId = $("#" + $(this).attr("toggle-ok-button"));
    if (toggleId.is(":checked")) {
        toggleId.prop("checked", false);
    } else {
        toggleId.prop("checked", true);
    }
    let toggleOkButton = $(this).attr("toggle-ok-button") + "-form";
    checkAlternativeCheckbox($(this).attr("toggle-ok-button"));
    submitStatusUpdateForm(toggleOkButton);
});
$("#toggle-status-custom-modal-ok-button").on("click", function() {
    const toggleId = $("#" + $(this).attr("toggle-ok-button"));
    if (toggleId.is(":checked")) {
        toggleId.prop("checked", false);
    } else {
        toggleId.prop("checked", true);
    }
    let toggleOkButton = $(this).attr("toggle-ok-button") + "-form";
    checkAlternativeCheckbox($(this).attr("toggle-ok-button"));
    submitStatusUpdateForm(toggleOkButton);
});

function submitStatusUpdateForm(formId) {
    const form = $("#" + formId);
    $.ajaxSetup({
        headers: {
            "X-CSRF-TOKEN": $('meta[name="_token"]').attr("content")
        }
    });
    let updateText = $("#get-update-status-message");
    $.ajax({
        url: form.attr("action"),
        method: form.attr("method"),
        data: form.serialize(),
        success: function(data) {
            switch (form.data("from")) {
                case "deal":
                    toastMagic.success(updateText.data("text"));
                    location.reload();
                    break;
                case "storage-connection-type":
                    if (data.status) {
                        toastMagic.success(updateText.data("text"));
                    } else {
                        toastMagic.error(data.message);
                    }
                    location.reload();
                    break;
                case "currency":
                    if (data.status) {
                        toastMagic.success(updateText.data("text"));
                    } else {
                        toastMagic.error(data.message);
                    }
                    location.reload();
                    break;
                case "default-withdraw-method-status":
                    let defaultWithdrawMethodMessage = $(
                        "#get-withdrawal-method-default-text"
                    );
                    if (data.success) {
                        toastMagic.success(
                            defaultWithdrawMethodMessage.data("success")
                        );
                    } else {
                        toastMagic.error(
                            defaultWithdrawMethodMessage.data("error")
                        );
                    }
                    location.reload();
                    break;

                case "withdraw-method-status":
                    if (data.success) {
                        toastMagic.success(updateText.data("text"));
                    } else {
                        toastMagic.error(updateText.data("error"));
                    }
                    location.reload();
                    break;

                case "featured-product-status":
                    toastMagic.success(
                        $("#get-featured-status-message").data("success")
                    );
                    break;
                case "product-status-update":
                    if (data.success) {
                        toastMagic.success(updateText.data("text"));
                    } else {
                        toastMagic.error(updateText.data("error"));
                        location.reload();
                    }
                    break;

                case "shop":
                case "delivery-restriction":
                case "default-language":
                    toastMagic.success(data.message);
                    location.reload();
                    break;
                case "product-status":
                    if (data.success) {
                        toastMagic.success(data.message);
                    } else {
                        toastMagic.error(data.message);
                        setTimeout(function() {
                            location.reload();
                        }, 2000);
                    }
                    break;
                case "maintenance-mode":
                    if (data.success) {
                        toastMagic.success(data.message);
                    } else {
                        toastMagic.info(data.message);
                        setTimeout(function() {
                            location.reload();
                        }, 1000);
                    }
                    break;
                case "all-page-banner":
                    if (data.status) {
                        toastMagic.success(data.message);
                    } else {
                        toastMagic.info(data.message);
                    }
                    setTimeout(function() {
                        location.reload();
                    }, 1000);
                    break;
                case "clearance-sale":
                    if (data.status) {
                        toastMagic.success(data.message);
                    } else if (!data.status) {
                        toastMagic.error(data.message);
                    } else {
                        toastMagic.info(data.message);
                    }
                    setTimeout(function() {
                        location.reload();
                    }, 2500);
                    break;
                default:
                    toastMagic.success(updateText.data("text"));
                    break;
            }
        }
    });
}

// $(".js-toggle-password").each(function () {
//     new HSTogglePassword(this).init();
// });

$(".delete-data").on("click", function() {
    let getText = $("#get-confirm-and-cancel-button-text-for-delete");
    Swal.fire({
        title: getText.data("sure"),
        text: getText.data("text"),
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        cancelButtonText: getText.data("cancel"),
        confirmButtonText: getText.data("confirm"),
        reverseButtons: true
    }).then(result => {
        if (result.value) {
            $("#" + $(this).data("id")).submit();
        }
    });
});

function deleteDataWithoutForm() {
    $(".delete-data-without-form").on("click", function() {
        let getText = $("#get-confirm-and-cancel-button-text-for-delete");
        let dataFrom = $(this).data("from");
        Swal.fire({
            title: getText.data("sure"),
            text: getText.data("text"),
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            cancelButtonText: getText.data("cancel"),
            confirmButtonText: getText.data("confirm"),
            reverseButtons: true
        }).then(result => {
            if (result.value) {
                $.ajaxSetup({
                    headers: {
                        "X-CSRF-TOKEN": $('meta[name="_token"]').attr("content")
                    }
                });
                let id = $(this).data("id");
                $.ajax({
                    url: $(this).data("action"),
                    method: "POST",
                    data: { id: id },
                    success: function(response) {
                        if (dataFrom == "currency") {
                            if (response.status == 1) {
                                toastMagic.success(
                                    $("#get-delete-currency-message").data(
                                        "success"
                                    )
                                );
                            } else {
                                toastMagic.warning(
                                    $("#get-delete-currency-message").data(
                                        "warning"
                                    )
                                );
                            }
                        } else {
                            toastMagic.success(
                                $("#get-deleted-message").data("text")
                            );
                        }

                        setTimeout(() => {
                            location.reload();
                        }, 2000);
                    }
                });
            }
        });
    });
}

deleteDataWithoutForm();

function locationReload() {
    $(".reload-by-onclick").on("click", function() {
        location.reload();
    });
}
locationReload();
$(".image-input").on("change", function() {
    let input = this;
    let img = document.getElementById($(this).data("image-id"));
    if (input.files && input.files[0]) {
        let reader = new FileReader();
        reader.onload = function(e) {
            if (img !== null) {
                img.src = e.target.result;
            }
            let imgName = input.files[0].name;
            if (input.closest("[data-title]")) {
                input
                    .closest("[data-title]")
                    .setAttribute("data-title", imgName);
            }
        };
        reader.readAsDataURL(input.files[0]);
    }
});

$(".copy-to-clipboard").on("click", function() {
    let copiedText = $($(this).data("id")).text();
    let tempInput = $("<textarea>");
    $("body").append(tempInput);
    tempInput.val(copiedText).select();
    document.execCommand("copy");
    tempInput.remove();
    toastMagic.success($("#get-copy-to-clipboard").data("success"));
});

// $(window).on("load", function () {
//     if ($(".instruction-carousel").length) {
//         let slideCount = $(".instruction-carousel .swiper-slide").length;
//         let swiperPaginationCustom = $(".instruction-pagination-custom");
//         let swiperPaginationAll = $(
//             ".instruction-pagination-custom, .instruction-pagination"
//         );
//         swiperPaginationCustom.html(`1 / ${slideCount}`);

//         var swiper = new Swiper(".instruction-carousel", {
//             autoHeight: true,
//             pagination: {
//                 el: ".instruction-pagination",
//                 clickable: true,
//             },
//             navigation: {
//                 nextEl: ".swiper-button-next",
//                 prevEl: ".swiper-button-prev",
//             },
//             on: {
//                 slideChange: () => {
//                     swiperPaginationCustom.html(
//                         `${swiper.realIndex + 1} / ${swiper.slidesGrid.length}`
//                     );
//                     if (swiper.isEnd) {
//                         swiperPaginationAll.css("display", "none");
//                     } else {
//                         swiperPaginationAll.css("display", "block");
//                     }
//                 },
//             },
//         });
//     }
// });

$(".onerror-chatting").on("error", function() {
    let image = $("#onerror-chatting").data("onerror-chatting");
    $(this).attr("src", image);
});

$(".onerror-user").on("error", function() {
    let image = $("#onerror-user").data("onerror-user");
    $(this).attr("src", image);
});

var backgroundImage = $("[data-bg-img]");
backgroundImage
    .css("background-image", function() {
        return 'url("' + $(this).data("bg-img") + '")';
    })
    .removeAttr("data-bg-img")
    .addClass("bg-img");

function onErrorImage() {
    $(".onerror-image").on("error", function() {
        let image = $(this).data("onerror");
        $(this).attr("src", image);
    });
}
onErrorImage();
$(window).on("load", function() {
    onErrorImage();
});

$(".get-customer-list-by-ajax-request").select2({
    data: [
        { id: "", text: "Select your option", disabled: true, selected: true }
    ],
    ajax: {
        url: $("#get-customer-list-route").data("action"),
        data: function(params) {
            return {
                searchValue: params.term, // search term
                page: params.page
            };
        },
        processResults: function(data) {
            return {
                results: data
            };
        },
        __port: function(params, success, failure) {
            var $request = $.ajax(params);

            $request.then(success);
            $request.fail(failure);

            return $request;
        }
    }
});
$(".get-customer-list-without-all-customer").select2({
    data: [
        { id: "", text: "Select your option", disabled: true, selected: true }
    ],
    dropdownParent: $('#add-fund-modal'),
    ajax: {
        url: $("#get-customer-list-without-all-customer-route").data("action"),
        data: function(params) {
            return {
                searchValue: params.term, // search term
                page: params.page
            };
        },
        processResults: function(data) {
            return {
                results: data
            };
        },
        __port: function(params, success, failure) {
            var $request = $.ajax(params);

            $request.then(success);
            $request.fail(failure);

            return $request;
        }
    }
});

$("#start-date-time,#end-date-time").change(function() {
    let from = $("#start-date-time");
    let to = $("#end-date-time");
    if (from.val() !== "" && to.val() !== "" && from.val() > to.val()) {
        from.val("");
        to.val("");
        toastMagic.error($("#get-date-range-message").data("error"));
    }
});

$(".set-customer-value").on("change", function() {
    $('input[name="customer_id"]').val($(this).val());
});

$(".withdraw-status-filter").on("change", function() {
    location.href =
        $(this).data("action") + "?" + "approved" + "=" + $(this).val();
});
$(".form-alert").on("click", function() {
    let getText = $("#get-confirm-and-cancel-button-text");
    Swal.fire({
        title: getText.data("sure"),
        text: $(this).data("message"),
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        cancelButtonText: getText.data("cancel"),
        confirmButtonText: getText.data("confirm"),
        reverseButtons: true
    }).then(result => {
        if (result.value) {
            $("#" + $(this).data("id")).submit();
        }
    });
});

$("#general-section").click(function() {
    $("#password-section").removeClass("active");
    $("#general-section").addClass("active");
    $("html, body").animate(
        {
            scrollTop: $("#general-div").offset().top
        },
        2000
    );
});
$("#password-section").click(function() {
    $("#general-section").removeClass("active");
    $("#password-section").addClass("active");
    $("html, body").animate(
        {
            scrollTop: $("#password-div").offset().top
        },
        2000
    );
});

$(".image-preview-before-upload").on("change", function() {
    let getElementId = $(this).data("preview");
    $(getElementId).attr("src", window.URL.createObjectURL(this.files[0]));
    $(getElementId).removeClass("d-none");
    $(getElementId)
        .closest(".custom_upload_input")
        .find(".placeholder-image")
        .css("opacity", "0");
});

$(document).on("ready", function() {
    if ($(".image-preview-before-upload").length) {
        $(".image-preview-before-upload").each(function() {
            if ($(this).data("image")) {
                let getElementId = $(this).data("preview");
                $(getElementId).attr("src", $(this).data("image"));
                $(getElementId).removeClass("d-none");
                $(getElementId)
                    .closest(".custom_upload_input")
                    .find(".placeholder-image")
                    .css("opacity", "0");
            }
        });
    }
});

var backgroundImage = $("[data-bg-img]");
backgroundImage
    .css("background-image", function() {
        return 'url("' + $(this).data("bg-img") + '")';
    })
    .removeAttr("data-bg-img")
    .addClass("bg-img");

$("#inhouse-vacation-start-date, #inhouse-vacation-end-date").change(
    function() {
        let elementFromDate = $("#inhouse-vacation-start-date");
        let elementToDate = $("#inhouse-vacation-end-date");
        let fromDate = elementFromDate.val();
        let toDate = elementToDate.val();
        if (fromDate !== "") {
            elementToDate.attr("required", "required");
        }
        if (toDate !== "") {
            elementFromDate.attr("required", "required");
        }
        if (fromDate !== "" && toDate !== "") {
            if (fromDate > toDate) {
                elementFromDate.val("");
                elementToDate.val("");
                toastMagic.error(
                    $("#message-invalid-date-range").data("text"),
                    Error,
                    {
                        CloseButton: true,
                        ProgressBar: true
                    }
                );
            }
        }
    }
);

$(".js-example-theme-single").select2({
    theme: "classic"
});

$(".js-example-responsive").select2({
    width: "resolve"
});

$(".update-status").on("click", function() {
    let id = $(this).data("id");
    let status = $(this).data("status");
    let getText = $("#get-confirm-and-cancel-button-text");
    let targetUrl = $(this).data("redirect-route");
    Swal.fire({
        title: getText.data("sure"),
        text: $(this).data("message"),
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        cancelButtonText: getText.data("cancel"),
        confirmButtonText: getText.data("confirm"),
        reverseButtons: true
    }).then(result => {
        if (result.value) {
            $.ajaxSetup({
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="_token"]').attr("content")
                }
            });
            $.ajax({
                url: $("#get-update-status-route").data("action"),
                method: "POST",
                data: {
                    id: id,
                    status: status
                },
                success: function(response) {
                    toastMagic.success(response.message);
                    if (targetUrl) {
                        location.href = targetUrl;
                    } else {
                        location.reload();
                    }
                }
            });
        }
    });
});

$(".action-update-product-quantity").on("click", function() {
    $.ajaxSetup({
        headers: {
            "X-CSRF-TOKEN": $('meta[name="_token"]').attr("content")
        }
    });
    let modalSelector = $(this).data("bs-target");
    $.ajax({
        method: "get",
        url: $(this).data("url"),
        dataType: "json",
        beforeSend: function() {
            $("#loading").fadeIn();
        },
        success: function(response) {
            $(".rest-part-content")
                .empty()
                .html(response.view);
            $(modalSelector).modal("show");
            updateProductQuantityByKeyUp();
        },
        complete: function() {
            $("#loading").fadeOut();
        }
    });
});

$(".action-onclick-reload-page").on("click", function() {
    location.reload();
});

$(".action-select-onchange-get-view").on("change", function() {
    let getUrlPrefix = $(this).data("url-prefix");
    location.href = getUrlPrefix + $(this).val();
});

$(".action-upload-section-dot-area").on("change", function() {
    if (this.files && this.files[0]) {
        let reader = new FileReader();
        reader.onload = () => {
            let imgName = this.files[0].name;
            $(this)
                .closest("[data-title]")
                .attr("data-title", imgName);
        };
        reader.readAsDataURL(this.files[0]);
    }
});

$(".action-print-invoice").on("click", function() {
    printDiv($(this).data("value"));
});

function printDiv(divName) {
    let printContents = document.getElementById(divName).innerHTML;
    let originalContents = document.body.innerHTML;
    document.body.innerHTML = printContents;
    window.print();
    document.body.innerHTML = originalContents;
}

$(".form-system-language-tab").on("click", function(e) {
    e.preventDefault();
    $(".form-system-language-tab").removeClass("active");
    $(".form-system-language-form").addClass("d-none");
    $(".form-system-sub-title-language-form").addClass("d-none");
    $(".form-system-description-language-form").addClass("d-none");
    $(this).addClass("active");
    let form_id = this.id;
    let lang = form_id.split("-")[0];
    $("#" + lang + "-form").removeClass("d-none");
    $("#" + lang + "-sub-title-form").removeClass("d-none");
    $("#" + lang + "-description-form").removeClass("d-none");
    $("." + lang + "-form").removeClass("d-none");
});

$(".form-dynamic-language-tab").on("click", function(e) {
    e.preventDefault();
    let commonClass = $(this).data("common");
    let targetSelector = $(this).data("target-selector");
    $(".form-dynamic-language-tab").removeClass("active");
    $(this).addClass("active");
    $(`.${commonClass}`).addClass("d-none");
    $(targetSelector).removeClass("d-none");
});

$(".open-info-web").on("click", function() {
    let websiteInfo = document.getElementById("website_info");
    if (websiteInfo.style.display === "none") {
        websiteInfo.style.display = "block";
    } else {
        websiteInfo.style.display = "none";
    }
});

$(window).on("load", function() {
    if ($(".navbar-vertical-content li.active").length) {
        $(".navbar-vertical-content").animate(
            {
                scrollTop:
                    $(".navbar-vertical-content li.active").offset().top - 150
            },
            10
        );
    }
});

let $rows = $(".navbar-vertical-content .navbar-nav > li");
$("#search-bar-input").keyup(function() {
    let val = $.trim($(this).val())
        .replace(/ +/g, " ")
        .toLowerCase();

    $rows
        .show()
        .filter(function() {
            let text = $(this)
                .text()
                .replace(/\s+/g, " ")
                .toLowerCase();
            return !~text.indexOf(val);
        })
        .hide();
});

$("#reset").on("click", function() {
    let placeholderImg = $("#placeholderImg").data("img");
    $("#viewer").attr("src", placeholderImg);
    $(".spartan_remove_row").click();
});

$(".check-order").on("click", function() {
    location.href = $("#get-orders-list-route").data("action");
});

$(".ignore-check-order").on("click", function() {
    $("#popup-modal")
        .appendTo("body")
        .modal("hide");
    let token = $('meta[name="_token"]').attr("content");
    $.ajaxSetup({
        headers: {
            "X-CSRF-TOKEN": token
        }
    });
    $.ajax({
        url: $("#get-orders-list-route").data("action"),
        type: "GET",
        data: {
            _token: token
        },
        success: function(response) {
            console.log(response);
        }
    });
});

$(document).mouseup(function(e) {
    let container = $("#search-card");
    if (!container.is(e.target) && container.has(e.target).length === 0) {
        container.hide();
    }
});

function getPageViewOnClick() {
    $(".get-view-by-onclick").on("click", function() {
        location.href = $(this).data("link");
    });
}
getPageViewOnClick();

function updateProductQuantity() {
    let elementCurrentStock = $('input[name="current_stock"]');
    let totalQuantity = 0;
    let quantityElements = $('input[name^="qty_"]');
    for (let i = 0; i < quantityElements.length; i++) {
        totalQuantity += parseInt(quantityElements.eq(i).val());
    }
    if (quantityElements.length > 0) {
        elementCurrentStock.attr("readonly", true);
        elementCurrentStock.val(totalQuantity);
    } else {
        elementCurrentStock.attr("readonly", false);
    }
}

function updateProductQuantityByKeyUp() {
    $('input[name^="qty_"]').on("keyup", function() {
        let qty_elements = $('input[name^="qty_"]');
        let totalQtyCheck = 0;
        let total_qty = 0;
        for (let i = 0; i < qty_elements.length; i++) {
            total_qty += parseInt(qty_elements.eq(i).val());
            totalQtyCheck += qty_elements.eq(i).val();
        }
        $('input[name="current_stock"]').val(total_qty);
        if (totalQtyCheck % 1) {
            toastMagic.warning(
                $("#get-quantity-check-message").data("warning")
            );
            $(this).val(parseInt($(this).val()));
        }
    });
    $('input[name="current_stock"]').on("keyup", function() {
        if ($(this).val() % 1) {
            toastMagic.warning(
                $("#get-quantity-check-message").data("warning")
            );
            $(this).val(parseInt($(this).val()));
        }
    });
}
updateProductQuantityByKeyUp();
$(".onsubmit-disable-action-button").on("submit", function() {
    $('.onsubmit-disable-action-button button[type="submit"]').attr(
        "disabled",
        true
    );
});
$(".reset-button").on("click", function() {
    $(".select-product-button").text(
        $("#get-select-product-text").data("text")
    );
});

$(".form-submit").on("click", function() {
    let getText = $("#get-confirm-and-cancel-button-text");
    let targetUrl = $(this).data("redirect-route");
    const getFormId = $(this).data("form-id");
    Swal.fire({
        title: getText.data("sure"),
        text: $(this).data("message"),
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        cancelButtonText: getText.data("cancel"),
        confirmButtonText: getText.data("confirm"),
        reverseButtons: true
    }).then(result => {
        if (result.value) {
            let formData = new FormData(document.getElementById(getFormId));
            $.ajaxSetup({
                headers: {
                    "X-XSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")
                }
            });
            $.post({
                url: $("#" + getFormId).attr("action"),
                data: formData,
                contentType: false,
                processData: false,
                beforeSend: function() {
                    $("#loading").fadeIn();
                },
                success: function(data) {
                    if (data.errors) {
                        for (let index = 0; index < data.errors.length; index++) {
                            setTimeout(() => {
                                toastMagic.error(data.errors[index].message);
                            }, index * 500);
                        }
                    } else if (data.error) {
                        toastMagic.error(data.error);
                    } else {
                        toastMagic.success(data.message);
                        if (targetUrl) {
                            location.href = targetUrl;
                        } else {
                            location.reload();
                        }
                    }
                },
                complete: function() {
                    $("#loading").fadeOut();
                }
            });
        }
    });
});
function removeSymbol() {
    $(".remove-symbol").on("keyup", function() {
        $(this).val(
            $(this)
                .val()
                .replace(/[^0-9]/g, "")
        );
    });
}
removeSymbol();

$("input[name=phone]").on("keyup keypress", function() {
    $(this).val(
        $(this)
            .val()
            .replace(/[^0-9]/g, "")
    );
});
$(".password-check").on("keyup keypress change click", function() {
    let password = $(this).val();
    let passwordError = $(".password-error");
    let passwordErrorMessage = $("#password-error-message");
    switch (true) {
        case password.length < 8:
            passwordError
                .html(passwordErrorMessage.data("max-character"))
                .removeClass("d-none");
            break;
        case !/[a-z]/.test(password):
            passwordError
                .html(passwordErrorMessage.data("lowercase-character"))
                .removeClass("d-none");
            break;
        case !/[A-Z]/.test(password):
            passwordError
                .html(passwordErrorMessage.data("uppercase-character"))
                .removeClass("d-none");
            break;
        case !/\d/.test(password):
            passwordError
                .html(passwordErrorMessage.data("number"))
                .removeClass("d-none");
            break;
        case !/[@.#$!%*?&]/.test(password):
            passwordError
                .html(passwordErrorMessage.data("symbol"))
                .removeClass("d-none");
            break;
        default:
            passwordError.addClass("d-none").empty();
    }
});

$(".product-gallery-filter").on("change", function() {
    let name = $(this).attr("name");
    let getData = $("#get-product-gallery-route");
    let brandId = name == "brand_id" ? $(this).val() : getData.data("brand-id");
    let categoryId =
        name == "category_id" ? $(this).val() : getData.data("category-id");
    let vendorId =
        name == "vendor_id" ? $(this).val() : getData.data("vendor-id");
    let urlParams = [];
    if (brandId) urlParams.push("brand_id=" + brandId);
    if (categoryId) urlParams.push("category_id=" + categoryId);
    if (vendorId) urlParams.push("vendor_id=" + vendorId);
    location.href = getData.data("action") + "?" + urlParams.join("&");
});
function countWords(str) {
    str = str.replace(/(^\s*)|(\s*$)/gi, "");
    str = str.replace(/[ ]{2,}/gi, " ");
    str = str.replace(/\n /, "\n");
    let matches = str.match(/[\w\d\’\'-]+/gi);
    return matches ? matches.length : 0;
}
function buttonDisableOrEnableFunction(className, status) {
    $("." + className).attr("disabled", status);
}
// function getWindowSize() {
//     let width =
//         window.innerWidth ||
//         document.documentElement.clientWidth ||
//         document.body.clientWidth;
//
//     let height =
//         window.innerHeight ||
//         document.documentElement.clientHeight ||
//         document.body.clientHeight;
//
//     return { width: width, height: height };
// }
$(".download-path-not-found").on("click", function(event) {
    toastMagic.error($("#download-path-not-found-message").data("message"));
});
$(document).ready(function() {
    $(".switcher_input_js").on("change", function() {
        $(".switcher_input_js")
            .not(this)
            .prop("checked", !$(this).is(":checked"));
        $(".custom_sorting_radio_list").slideToggle();
    });
});
$(document).ready(function() {
    $(".switcher-input-js").on("change", function() {
        $("." + $(this).data("parent-class") + " " + ".switcher-input-js")
            .not(this)
            .prop("checked", !$(this).is(":checked"));
        let customSortingSection =
            "." +
            $(this).data("parent-class") +
            " " +
            ".custom-sorting-radio-list";
        if (
            $(this).data("from") === "custom-sorting" &&
            !$(this).is(":checked")
        ) {
            $(customSortingSection).removeClass("d--none");
        } else if (
            $(this).data("from") === "default-sorting" &&
            $(this).is(":checked")
        ) {
            $(customSortingSection).removeClass("d--none");
        } else {
            $(customSortingSection).addClass("d--none");
        }
        $(customSortingSection).slideToggle();
    });
    $(".check-box").on("change", function() {
        $("." + $(this).data("parent-class") + " " + ".check-box")
            .not(this)
            .prop("checked", !$(this).is(":checked"));
    });
});

function productStockLimitStatus() {
    try {
        let productStockAction = $("#get-stock-limit-status").data("action");
        let getImage = $("#get-product-stock-limit-image");
        let getTitle = $("#get-product-stock-limit-title");
        let getMessage = $("#get-product-stock-limit-message");
        let productStockView = $("#get-product-stock-view");
        $.get({
            url: productStockAction,
            dataType: "json",
            success: function(response) {
                if (response.status === "one_product") {
                    $(".product-limited-stock-alert .image")
                        .attr("src", "")
                        .attr("src", response.thumbnail);
                    $(".product-limited-stock-alert .image").attr("width", 50);
                    $(".product-limited-stock-alert .title")
                        .empty()
                        .html(response.product.name);
                    $(".product-limited-stock-alert .message")
                        .empty()
                        .html(getMessage.data("message-for-one-product"));
                    $(".product-limited-stock-alert .product-list")
                        .attr("href", "")
                        .attr(
                            "href",
                            productStockView.data("stock-limit-page")
                        );
                } else {
                    $(".product-limited-stock-alert .image")
                        .attr("src", "")
                        .attr("src", getImage.data("warning-image"));
                    $(".product-limited-stock-alert .title")
                        .empty()
                        .html(getTitle.data("title"));
                    $(".product-limited-stock-alert .message")
                        .empty()
                        .html(
                            response.product_count <= 100
                                ? response.product_count -
                                      1 +
                                      "+ " +
                                      getMessage.data(
                                          "message-for-three-plus-product"
                                      )
                                : getMessage.data("message-for-multiple")
                        );
                    $(".product-limited-stock-alert .product-list")
                        .attr("href", "")
                        .attr(
                            "href",
                            productStockView.data("stock-limit-page")
                        );
                }
                if (response.product_count > 0) {
                    $(".product-limited-stock-alert").addClass("active");
                }
            }
        });
    } catch (exception) {
        console.info(exception);
    }
}

$(".product-stock-limit-close").on("click", function() {
    $(".product-limited-stock-alert").removeClass("active");
});

$(document).ready(function() {
    if (
        document.cookie.indexOf("6valley_stock_limit_status=accepted") !== -1 ||
        document.cookie.indexOf("6valley_stock_limit_status=reject") !== -1
    ) {
        $(".product-limited-stock-alert").hide();
    } else {
        productStockLimitStatus();
        setInterval(productStockLimitStatus, 600000);
    }
});

$(document).on("click", ".product-stock-alert-hide", function() {
    document.cookie =
        "6valley_stock_limit_status=accepted; max-age=" +
        60 * 60 * 24 * 30 +
        "; path=/";
    $(".product-limited-stock-alert").hide();
});

$(document).on("click", ".product-stock-limit-close", function() {
    document.cookie =
        "6valley_stock_limit_status=reject; max-age=" + 60 * 20 + "; path=/";
    $(".product-limited-stock-alert").hide();
});

$("#payment-gateway-cards input[name=status]").on("change", function() {
    $(this).val($(this).prop("checked") ? 1 : 0);
});

$("#xml_file_input").on("change", function() {
    $("#xml_file_upload_placeholder").addClass("d-none");
    $("#xml_file_upload_progress").removeClass("d-none");
    setTimeout(() => {
        $("#xml_file_upload_submit").attr("disabled", false);
        xmlFileUploadProgressBar();
    }, 1000);
});

$("#xml_file_upload_cancel").on("click", function() {
    $("#xml_file_upload_submit").attr("disabled", true);
    $("#xml_file_upload_placeholder").removeClass("d-none");
    $("#xml_file_upload_progress").addClass("d-none");
    $("#xml_file_upload_form").trigger("reset");
    xmlFileUploadCancelProcess();
});

$(".xml_file_upload_close").on("click", function() {
    $("#xml_file_upload_submit").attr("disabled", true);
    $("#xml_file_upload_placeholder").removeClass("d-none");
    $("#xml_file_upload_progress").addClass("d-none");
    $("#xml_file_upload_form").trigger("reset");
    xmlFileUploadCancelProcess();
});

$("#xml_file_upload_progress .xml_file_upload_cancel_icon").on(
    "click",
    function() {
        xmlFileUploadCancelProcess();
    }
);

function xmlFileUploadProgressBar() {
    let initialValue = 0;
    const xmlProgressBar = setInterval(() => {
        let progressTextElement = $("#xml_file_upload_progress .progress-text");
        let progressBarElement = $("#xml_file_upload_progress .progress-bar");
        let progressText = "";
        if (initialValue < 100) {
            initialValue++;
            progressBarElement.attr("style", "width:" + initialValue + "%");
            progressText =
                initialValue + "% " + progressTextElement.data("progress");
            progressBarElement.removeClass("bg-success");
        } else {
            progressText =
                initialValue + "% " + progressTextElement.data("complete");
            clearInterval(xmlProgressBar);
            setTimeout(() => {
                progressBarElement.addClass("bg-success");
            }, 500);
        }
        progressTextElement.html(progressText);
    }, 5);
}

function xmlFileUploadCancelProcess() {
    let progressTextElement = $("#xml_file_upload_progress .progress-text");
    let progressBarElement = $("#xml_file_upload_progress .progress-bar");
    progressTextElement.html("0% " + progressTextElement.data("progress"));
    progressBarElement.attr("style", "width:0%");

    $("#xml_file_upload_submit").attr("disabled", true);
    $("#xml_file_upload_placeholder").removeClass("d-none");
    $("#xml_file_upload_progress").addClass("d-none");
    $("#xml_file_upload_form").trigger("reset");
}

$(".getDownloadFileUsingFileUrl").on("click", function() {
    let getLink = $(this).data("file-path");
    downloadFileUsingFileUrl(getLink);
});

function downloadFileUsingFileUrl(url) {
    fetch(url)
        .then(response => response.blob())
        .then(blob => {
            const filename = url.substring(url.lastIndexOf("/") + 1);
            const blobUrl = window.URL.createObjectURL(new Blob([blob]));
            const link = document.createElement("a");
            link.href = blobUrl;
            link.setAttribute("download", filename);
            document.body.appendChild(link);
            link.click();
            link.parentNode.removeChild(link);
        })
        .catch(error => console.error("Error downloading file:", error));
}

$(window).on("load", function() {
    $(".date-range-js")
        .daterangepicker({
            autoUpdateInput: false,
            locale: {
                format: "MMMM D, YYYY",
                cancelLabel: "Clear"
            }
        })
        .on("apply.daterangepicker", function(ev, picker) {
            $(this).val(
                picker.startDate.format("MMMM D, YYYY") +
                    " - " +
                    picker.endDate.format("MMMM D, YYYY")
            );
        })
        .on("cancel.daterangepicker", function(ev, picker) {
            $(this).val("");
            $(this).attr("placeholder", "Select Date");
            $(this)
                .data("daterangepicker")
                .setStartDate(moment());
            $(this)
                .data("daterangepicker")
                .setEndDate(moment());
        })
        .attr("placeholder", "Select date");
});

$("#robotsMetaContentPageSelect").on("change", function() {
    robotsMetaContentPageSelect();
});

function robotsMetaContentPageSelect() {
    let value = $("#robotsMetaContentPageSelect").val();
    let routePath = $("#robotsMetaContentPageURoutes").data(
        value.toLowerCase()
    );
    $("#robotsMetaContentPageUrl").val(routePath);
}

$(".action-input-no-index-event").on("click", function() {
    $(".input-no-index-sub-element").prop("checked", true);
});

$(document).on("click", ".product-restock-request-alert-hide", function() {
    document.cookie = "6valley_restock_request_status=accepted; path=/";
    $(".product-restock-stock-alert").hide();
});

function productRestockStockLimitStatus(response) {
    let mainElement = $(".product-restock-stock-alert");
    mainElement.find(".title").html(response?.title);
    mainElement
        .find(".image")
        .attr("width", 50)
        .attr("src", response?.image);
    mainElement.find(".message").html(response?.body);
    mainElement.find(".product-link").attr("data-link", response?.route);
    mainElement.addClass("active");
}

$(".product-restock-stock-close").on("click", function() {
    document.cookie = "6valley_restock_request_status=accepted; path=/";
    $(".product-restock-stock-alert").hide();
});

function validateDateRangePickerDateInput(e) {
    if (
        $.inArray(e.keyCode, [46, 8, 9, 27, 13]) !== -1 ||
        (e.keyCode === 65 && e.ctrlKey === true) ||
        (e.keyCode >= 35 && e.keyCode <= 39)
    ) {
        return;
    }
    if (
        (e.shiftKey || e.keyCode < 48 || e.keyCode > 57) &&
        (e.keyCode < 96 || e.keyCode > 105) &&
        e.keyCode !== 191
    ) {
        e.preventDefault();
    }
}

function changeInputTypeForDateRangePicker(element) {
    try {
        element.on("keydown", function(event) {
            validateDateRangePickerDateInput(event);
        });

        if (element.val()) {
            var dateRangePicker = $(".js-daterangepicker-with-range");
            dateRangePicker
                .removeAttr("readonly")
                .removeClass("cursor-pointer");
        }
    } catch (e) {}
}

function ajaxSetupToken() {
    $.ajaxSetup({
        headers: {
            "X-XSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")
        }
    });
}

function checkFirebaseAuthVerification() {
    var firebaseAuthVerification = true;
    let checkbox = $(".firebase-auth-verification");
    if (checkbox.prop("checked")) {
        ajaxSetupToken();
        $.post({
            url: checkbox.data("route"),
            data: {
                _token: $('meta[name="csrf-token"]').attr("content"),
                key: checkbox.data("key")
            },
            async: false,
            beforeSend: function() {},
            success: function(response) {
                if (response?.status === false) {
                    $("#firebaseAuthConfigValidation .modal-body")
                        .empty()
                        .html(response?.htmlView);
                    $("#firebaseAuthConfigValidation").modal("show");
                    firebaseAuthVerification = false;
                } else {
                    checkbox.prop("checked", !checkbox.prop("checked"));
                }
            },
            complete: function() {}
        });
    }
    return firebaseAuthVerification;
}

$(".clearance-product-add-submit").on("click", function() {
    let form = $(".clearance-add-product")[0];
    let formData = new FormData(form);
    ajaxSetupToken();
    $.post({
        url: $(".clearance-add-product").attr("action"),
        data: formData,
        contentType: false,
        processData: false,
        beforeSend: function() {
            $("#loading").fadeIn();
        },
        success: function(response) {
            if (response.status) {
                $("#product-add-modal").modal("hide");
                toastMagic.success(response.message);
                setTimeout(() => {
                    location.reload();
                }, 3000);
            } else {
                $("#product-add-modal").modal("show");
                toastMagic.error(response.message);
            }
        },
        timeout: 5000,
        complete: function() {
            $("#loading").fadeOut();
        }
    });
});

$(".stock-clearance-delete-data").on("click", function() {
    let getText = $("#get-confirm-and-cancel-button-text-for-delete");
    Swal.fire({
        title: getText.data("sure"),
        text: getText.data("text"),
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        cancelButtonText: getText.data("cancel"),
        confirmButtonText: getText.data("confirm"),
        reverseButtons: true
    }).then(result => {
        if (result.value) {
            $("#" + $(this).data("id")).submit();
        }
    });
});

$(".stock-clearance-delete-data").on("click", function () {
    let getText = $("#get-confirm-and-cancel-button-text-for-delete");
    Swal.fire({
        title: getText.data("sure"),
        text: getText.data("text"),
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        cancelButtonText: getText.data("cancel"),
        confirmButtonText: getText.data("confirm"),
        reverseButtons: true,
    }).then((result) => {
        if (result.value) {
            $("#" + $(this).data("id")).submit();
        }
    });
});


$(".discount-amount-submit").on("click", function() {
    let form = $(".discount-amount-update")[0];
    let formData = new FormData(form);
    ajaxSetupToken();
    $.post({
        url: $(".discount-amount-update").attr("action"),
        data: formData,
        contentType: false,
        processData: false,
        beforeSend: function() {
            $("#loading").fadeIn();
        },
        success: function(response) {
            console.log(response);
            if (response.status) {
                $("#discount-update-modal").modal("hide");
                toastMagic.success(response.message);
                location.reload();
            } else {
                $("#discount-update-modal").modal("show");
                toastMagic.error(response.message);
            }
        },
        complete: function() {
            $("#loading").fadeOut();
        }
    });
});

$(".stock-clearance-delete-all-products").on("click", function() {
    let getText = $(
        "#get-confirm-and-cancel-button-text-for-delete-all-products"
    );
    Swal.fire({
        title: getText.data("sure"),
        text: getText.data("text"),
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        cancelButtonText: getText.data("cancel"),
        confirmButtonText: getText.data("confirm"),
        reverseButtons: true
    }).then(result => {
        console.log(result);
        if (result.value) {
            $("#" + $(this).data("id")).submit();
        }
    });
});

$("#payment-methods-settings-form").on("submit", function(event) {
    event.preventDefault();
    if (
        !$("#cash-on-delivery").prop("checked") &&
        !$("#digital-payment").prop("checked") &&
        !$("#offline-payment").prop("checked")
    ) {
        $("#active-one-method-modal").modal("show");
        return false;
    }
    $.ajaxSetup({
        headers: {
            "X-CSRF-TOKEN": $('meta[name="_token"]').attr("content")
        }
    });
    $.ajax({
        type: $(this).attr("method"),
        url: $(this).data("action"),
        data: $(this).serialize(),
        success: function(response) {
            if (response?.status === "success") {
                toastMagic.success(response?.message);
                location.reload();
            } else if (response?.status === "warning") {
                let modal = $("#minimum-one-digital-payment");
                modal.find(".modal-title").html(response?.title);
                modal.find(".modal-message").html(response?.message);

                if (response?.error_type === "minimum-one-digital-payment") {
                    $(".minimum-one-digital-payment").show();
                    $(".minimum-one-offline-payment-method").hide();
                } else if (
                    response?.error_type ===
                    "minimum-one-offline-payment-method"
                ) {
                    $(".minimum-one-digital-payment").hide();
                    $(".minimum-one-offline-payment-method").show();
                } else if (
                    response?.error_type === "digital-payment-status-required"
                ) {
                    $(".minimum-one-digital-payment").hide();
                    $(".minimum-one-offline-payment-method").hide();
                }

                $("#minimum-one-digital-payment").modal("show");
            } else {
                toastMagic.error(response?.message);
                location.reload();
            }
        }
    });
});

$(document).ready(function() {
    $("#discount_type").on("change", function() {
        let discountType = $(this).val();
        const symbol =
            discountType === "percentage"
                ? "(%)"
                : `(${$("#dynamic-currency-symbol").val()})`;
        $("#discount-symbol").html(symbol);
    });
});

$('[pattern="[0-9]*"]').on("keypress", function(event) {
    // Allow only numeric keys (0-9)
    if (event.which < 48 || event.which > 57) {
        event.preventDefault();
    }
});
