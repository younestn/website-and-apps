"use strict";

$(".delete-data").on("click", function () {
    let getText = $("#get-confirm-and-cancel-button-text-for-delete-country-name");
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
        element.on("keydown", function (event) {
            validateDateRangePickerDateInput(event);
        });

        if (element.val()) {
            var dateRangePicker = $(".js-daterangepicker-with-range");
            dateRangePicker
                .removeAttr("readonly")
                .removeClass("cursor-pointer");
        }
    } catch (e) {
    }
}

function submitStatusUpdateForm(formId) {
    const form = $("#" + formId);
    $.ajaxSetup({
        headers: {
            "X-CSRF-TOKEN": $('meta[name="_token"]').attr("content"),
        },
    });
    let updateText = $("#get-update-status-message");
    $.ajax({
        url: form.attr("action"),
        method: form.attr("method"),
        data: form.serialize(),
        success: function (data) {
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
                        setTimeout(function () {
                            location.reload();
                        }, 2000);
                    }
                    break;
                case "maintenance-mode":
                    if (data.success) {
                        toastMagic.success(data.message);
                    } else {
                        toastMagic.info(data.message);
                        setTimeout(function () {
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
                    setTimeout(function () {
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
                    setTimeout(function () {
                        location.reload();
                    }, 2500);
                    break;
                default:
                    toastMagic.success(updateText.data("text"));
                    break;
            }
        },
    });
}

function deleteDataWithoutForm() {
    $(".delete-data-without-form").on("click", function () {
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
            reverseButtons: true,
        }).then((result) => {
            if (result.value) {
                $.ajaxSetup({
                    headers: {
                        "X-CSRF-TOKEN": $('meta[name="_token"]').attr(
                            "content"
                        ),
                    },
                });
                let id = $(this).data("id");
                $.ajax({
                    url: $(this).data("action"),
                    method: "POST",
                    data: {id: id},
                    success: function (response) {
                        if (dataFrom == "currency") {
                            if (response.status == 1) {
                                toastMagic.success(
                                    $("#get-delete-currency-message").data(
                                        "success"
                                    )
                                );
                            } else {
                                toastMagic.warning($("#get-delete-currency-message").data("warning"));
                            }
                        } else {
                            // toastMagic.success(
                            //     $("#get-deleted-message").data("text")
                            // );
                        }

                        setTimeout(() => {
                            location.reload();
                        }, 2000);
                    },
                });
            }
        });
    });
}

deleteDataWithoutForm();

$(".form-submit").on("click", function () {
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
        reverseButtons: true,
    }).then((result) => {
        if (result.value) {
            let formData = new FormData(document.getElementById(getFormId));
            $.ajaxSetup({
                headers: {
                    "X-XSRF-TOKEN": $('meta[name="csrf-token"]').attr(
                        "content"
                    ),
                },
            });
            $.post({
                url: $("#" + getFormId).attr("action"),
                data: formData,
                contentType: false,
                processData: false,
                beforeSend: function () {
                    $("#loading").fadeIn();
                },
                success: function (data) {
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
                        setTimeout(() => {
                            if (targetUrl) {
                                location.href = targetUrl;
                            } else {
                                location.reload();
                            }
                        }, 2000)
                    }
                },
                complete: function () {
                    $("#loading").fadeOut();
                },
            });
        }
    });
});

$('.reset-form').on('click', function () {
    window.location.reload();
});

$("#robotsMetaContentPageSelect").on("change", function () {
    robotsMetaContentPageSelect();
});

function robotsMetaContentPageSelect() {
    let value = $("#robotsMetaContentPageSelect").val();
    let routePath = $("#robotsMetaContentPageURoutes").data(
        value.toLowerCase()
    );
    $("#robotsMetaContentPageUrl").val(routePath);
}

$('.no-reload-form').on('submit', function (e) {
    e.preventDefault();
    const $form = $(this);

    $.ajaxSetup({
        headers: {
            "X-CSRF-TOKEN": $('meta[name="_token"]').attr("content"),
        },
    });

    $.ajax({
        url: $form.attr('action'),
        method: 'POST',
        data: $form.serialize(),
        success: function (response) {
            toastMagic.success(response.message);

            if ($form.hasClass('reload-true')) {
                setTimeout(() => {
                    location.reload();
                }, 2000);
            }
        },
    });
});



$(document).ready(function () {

    // reset dropdown value
    $('button[type="reset"]').on('click', function () {
        const form = $(this).closest('form');
        setTimeout(function () {
            form.find('select.custom-select').each(function () {
                const defaultVal = $(this).find('option[selected]').val() || '';
                $(this).val(defaultVal).trigger('change.select2');
            });
            const themeRatio = form.find('#theme_ratio');
            if (themeRatio.length) {
                themeRatio.html('');
            }
        }, 10);
    });

    $('.js-daterangepicker').each(function () {
        const $input = $(this);
        const isPreviousDateAllowed = $input.hasClass('previous-date-true');
        const isPlaceholderMode = $input.hasClass('placeholder-mode-true'); // new check

        $input.daterangepicker({
            autoUpdateInput: !isPlaceholderMode, // If placeholder mode, don't auto update
            locale: {
                format: 'DD MMM YYYY'
            },
            minDate: !isPreviousDateAllowed ? moment() : false
        });

        if (isPlaceholderMode) {
            $input.attr('placeholder', 'DD MMM YYYY - DD MMM YYYY');
            $input.on('apply.daterangepicker', function (ev, picker) {
                $(this).val(picker.startDate.format('DD MMM YYYY') + ' - ' + picker.endDate.format('DD MMM YYYY'));
            });
            $input.on('cancel.daterangepicker', function (ev, picker) {
                $(this).val('');
            });
        }
    });

    let checkIsOffcanvasSetupDataValue = $('#check-offcanvas-setup-guide').data('value')?.toString();
    let checkIsOffcanvasSetupGuideEnable = checkIsOffcanvasSetupDataValue === 'true' || checkIsOffcanvasSetupDataValue === '1';

    if (checkIsOffcanvasSetupGuideEnable) {
        setTimeout(() => {
            const url = new URL(window.location.href);
            url.searchParams.delete('offcanvasShow');
            window.history.replaceState({}, document.title, url.toString());
        }, 3000);
    }
});
function updateProductQuantityByKeyUp() {
    $('input[name^="qty_"]').on("keyup", function () {
        let qty_elements = $('input[name^="qty_"]');
        let totalQtyCheck = 0;
        let total_qty = 0;
        for (let i = 0; i < qty_elements.length; i++) {
            total_qty += parseInt(qty_elements.eq(i).val());
            totalQtyCheck += qty_elements.eq(i).val();
        }
        $('input[name="current_stock"]').val(total_qty);
        if (totalQtyCheck % 1) {
            toastMagic.warning($("#get-quantity-check-message").data("warning"));
            $(this).val(parseInt($(this).val()));
        }
    });
    $('input[name="current_stock"]').on("keyup", function () {
        if ($(this).val() % 1) {
            toastMagic.warning($("#get-quantity-check-message").data("warning"));
            $(this).val(parseInt($(this).val()));
        }
    });
}

updateProductQuantityByKeyUp();

function reinitializeTooltips() {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
}


