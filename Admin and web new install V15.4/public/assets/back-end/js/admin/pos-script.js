"use strict";

let elementViewAllHoldOrdersSearch = $(".view_all_hold_orders_search");
let getYesWord = $("#message-yes-word").data("text");
let getNoWord = $("#message-no-word").data("text");
let messageAreYouSure = $("#message-are-you-sure").data("text");

document.addEventListener("keydown", function (event) {
    if (event.altKey && event.code === "KeyO") {
        $("#submit_order").click();
        event.preventDefault();
    }
    if (event.altKey && event.code === "KeyZ") {
        $("#payment_close").click();
        event.preventDefault();
    }
    if (event.altKey && event.code === "KeyS") {
        $("#order_complete").click();
        event.preventDefault();
    }
    if (event.altKey && event.code === "KeyC") {
        emptyCart();
        event.preventDefault();
    }
    if (event.altKey && event.code === "KeyA") {
        $("#add_new_customer").click();
        event.preventDefault();
    }
    if (event.altKey && event.code === "KeyN") {
        $("#submit_new_customer").click();
        event.preventDefault();
    }
    if (event.altKey && event.code === "KeyK") {
        $("#short-cut").click();
        event.preventDefault();
    }
    if (event.altKey && event.code === "KeyP") {
        $("#print_invoice").click();
        event.preventDefault();
    }
    if (event.altKey && event.code === "KeyQ") {
        $("#search").focus();
        $("#-pos-search-box").css("display", "none");
        event.preventDefault();
    }
    if (event.altKey && event.code === "KeyE") {
        $("#pos-search-box").css("display", "none");
        $("#extra_discount").click();
        event.preventDefault();
    }
    if (event.altKey && event.code === "KeyD") {
        $("#pos-search-box").css("display", "none");
        $("#coupon_discount").click();
        event.preventDefault();
    }
    if (event.altKey && event.code === "KeyB") {
        $("#invoice_close").click();
        event.preventDefault();
    }
    if (event.altKey && event.code === "KeyX") {
        $(".action-clear-cart").click();
        event.preventDefault();
    }
    if (event.altKey && event.code === "KeyR") {
        $(".action-new-order").click();
        event.preventDefault();
    }
});

$(".action-pos-update-quantity").on("focus", function () {
    $(this).select();
});


$(".search-bar-input").on("keyup", function () {
    $(".pos-search-card").removeClass("d-none").show();
    let name = $(".search-bar-input").val();
    let elementSearchResultBox = $(".search-result-box");
    if (name.length > 0) {
        $("#pos-search-box").removeClass("d-none").show();
        $.get({
            url: $("#route-admin-products-search-product").data("url"),
            dataType: "json",
            data: {
                name: name,
            },
            beforeSend: function () {
                $("#loading").fadeIn();
            },
            success: function (data) {
                elementSearchResultBox.empty().html(data.result);
                renderSelectProduct();
                renderQuickViewSearchFunctionality();
            },
            complete: function () {
                $("#loading").fadeOut();
            },
        });
    } else {
        elementSearchResultBox.empty().hide();
    }
});

$(".action-category-filter").on("change", (event) => {
    let getUrl = new URL(window.location.href);
    getUrl.searchParams.set("category_id", $(event.target).val());
    window.location.href = getUrl.toString();
});

function renderCustomerAmountForPay() {
    if (
        parseFloat($(".customer-wallet-balance").val()) <
        parseFloat($(".total-amount").val())
    ) {
        disableOrderPlaceButton();
        $(".wallet-balance-input").addClass("border-danger");
    } else {
        $(".wallet-balance-input").removeClass("border-danger");
    }
}

function disableOrderPlaceButton() {
    var selectedPaymentType = $('input[name="type"]:checked').val();
    if (selectedPaymentType === "wallet") {
        $(".action-form-submit").attr("disabled", true);
    } else {
        $(".action-form-submit").attr("disabled", false);
    }
}
$(".action-customer-change").on("change", function () {
    $.post({
        url: $("#route-admin-pos-change-customer").data("url"),
        data: {
            _token: $('meta[name="_token"]').attr("content"),
            user_id: $(this).val(),
        },
        beforeSend: function () {
            $("#loading").fadeIn();
        },
        success: function (data) {
            $("#cart-summary").empty().html(data.view);
            reinitializeTooltips();
            viewAllHoldOrders("keyup");
            basicFunctionalityForCartSummary();
            posUpdateQuantityFunctionality();
            removeFromCart();
            renderCustomerAmountForPay();
        },
        complete: function () {
            $("#loading").fadeOut();
        },
    });
});

$(".action-view-all-hold-orders").on("click", () => viewAllHoldOrders());
elementViewAllHoldOrdersSearch.on("input", () => viewAllHoldOrders("keyup"));

function viewAllHoldOrders(action = null) {
    $.ajaxSetup({
        headers: {
            "X-CSRF-TOKEN": $('meta[name="_token"]').attr("content"),
        },
    });
    $.post({
        url: $("#route-admin-pos-view-hold-orders").data("url"),
        data: {
            customer: elementViewAllHoldOrdersSearch.val(),
        },
        beforeSend: function () {
            $("#loading").fadeIn();
        },
        success: function (data) {
            $("#hold-orders-modal-content").empty().html(data.view);
            if (action !== "keyup") {
                $("#hold-orders-modal-btn").click();
            }
            $(".total_hold_orders").text(data.totalHoldOrders);
            renderViewHoldOrdersFunctionality();
            basicFunctionalityForCartSummary();
            posUpdateQuantityFunctionality();
        },
        complete: function () {
            $("#loading").fadeOut();
        },
    });
}

function renderSelectProduct() {
    $(".action-get-variant-for-already-in-cart").on("click", function () {
        getVariantForAlreadyInCart($(this).data("action"));
    });

    $(".action-add-to-cart").on("click", function (e) {
        addToCart();
    });

    $(".action-color-change").on("click", function () {
        let val = $(this).val();
        $(".color-border").removeClass("border-add");
        $("#label-" + val.id).addClass("border-add");
    });

    cartQuantityInitialize();
    getVariantPrice();
    $(".variant-change input , .cart-qty-field").on("change", function () {
        getVariantPrice();
    });
    $("#add-to-cart-form .in-cart-quantity-field").on("change", function () {
        getVariantPrice("already_in_cart");
    });

    $(".cart-qty-field").focus(function () {
        $(this).closest(".product-quantity-group").addClass("border-primary");
    });

    $(".cart-qty-field").blur(function () {
        $(this)
            .closest(".product-quantity-group")
            .removeClass("border-primary");
    });

    $(".in-cart-quantity-field").focus(function () {
        $(this).closest(".product-quantity-group").addClass("border-primary");
    });

    $(".in-cart-quantity-field").blur(function () {
        $(this)
            .closest(".product-quantity-group")
            .removeClass("border-primary");
    });
}

renderSelectProduct();
renderQuickViewFunctionality();

function renderQuickViewFunctionality() {
    $(".action-select-product").on("click", function () {
        quickView($(this).data("id"));
    });
}

function renderQuickViewSearchFunctionality() {
    $(".action-select-search-product").on("click", function () {
        quickView($(this).data("id"));
    });
}


document.addEventListener('click', function(event) {
    if (event.target.classList.contains('action-empty-alert-show')) {
        toastMagic.warning($("#message-cart-is-empty").data("text"));
    }
});


function basicFunctionalityForCartSummary() {
    $(".action-clear-cart").on("click", () => {
        document.location.href = $("#route-admin-pos-clear-cart-ids").data(
            "url"
        );
    });

    $(".action-new-order").on("click", () => {
        Swal.fire({
            title: messageAreYouSure,
            text: $("#message-you-want-to-create-new-order").data("text"),
            icon: "warning",
            showCancelButton: true,
            cancelButtonColor: "#dd3333",
            confirmButtonColor: "#161853",
            cancelButtonText: getNoWord,
            confirmButtonText: getYesWord,
            reverseButtons: true,
        }).then((result) => {
            if (result.value) {
                document.location.href = $("#route-admin-pos-new-cart-id").data(
                    "url"
                );
            }
        });
    });

    $(".action-cart-change").on("click", function () {
        let value = $(this).data("cart");
        let dynamicUrl = $("#route-admin-pos-change-cart-editable").data("url");
        dynamicUrl = dynamicUrl.replace(":value", `${value}`);
        window.location.href = dynamicUrl;
    });

    $(".action-empty-cart").on("click", function () {
        Swal.fire({
            title: messageAreYouSure,
            text: $("#message-you-want-to-remove-all-items-from-cart").data(
                "text"
            ),
            icon: "warning",
            showCancelButton: true,
            cancelButtonColor: "#dd3333",
            confirmButtonColor: "#161853",
            cancelButtonText: getNoWord,
            confirmButtonText: getYesWord,
            reverseButtons: true,
        }).then((result) => {
            if (result.value) {
                $.post(
                    $("#route-admin-pos-empty-cart").data("url"),
                    {
                        _token: $('meta[name="_token"]').attr("content"),
                    },
                    function (data) {
                        $("#cart-summary").empty().html(data.view);
                        toastMagic.info(
                            $("#message-item-has-been-removed-from-cart").data(
                                "text"
                            )
                        );
                    }
                );
            }
        });
    });

    $(".action-form-submit").on("click", function () {
        if (checkedPaidAmount()) {
            Swal.fire({
                title: messageAreYouSure,
                icon: "warning",
                text: $(this).data("message"),
                showCancelButton: true,
                showConfirmButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                cancelButtonText: getNoWord,
                confirmButtonText: getYesWord,
                reverseButtons: true,
            }).then(function (result) {
                if (result.value) {
                    let formData = new FormData(
                        document.getElementById("order-place")
                    );
                    $.ajaxSetup({
                        headers: {
                            "X-XSRF-TOKEN": $('meta[name="csrf-token"]').attr(
                                "content"
                            ),
                        },
                    });
                    $.post({
                        url: $("#order-place").attr("action"),
                        data: formData,
                        contentType: false,
                        processData: false,
                        beforeSend: function () {
                            $("#loading").fadeIn();
                        },
                        success: function (response) {
                            if (Boolean(response.checkProductTypeForWalkingCustomer) === true) {
                                $(".alert--message-for-pos").addClass("active");
                                $(".alert--message-for-pos .warning-message").empty().html(response.message);
                                $("#add-customer").modal("show");
                                setTimeout(() => {
                                    $('.alert--message-for-pos').removeClass('show').removeClass('active');
                                }, 5000)
                            } else {
                                location.reload();
                            }
                        },
                        complete: function () {
                            $("#loading").fadeOut();
                        },
                    });
                }
            });
        }
    });

    $(".option-buttons input").on("change", function () {
        renderCustomerAmountForPay();
        let type = $(this).val();
        if ($(this).is(":checked")) {
            $(".cash-change-section").hide();
            if (type === "cash") {
                $(".cash-change-amount").show();
            } else if (type === "card") {
                $(".cash-change-card").removeClass("d-none").show();
            } else if (type === "wallet") {
                let insufficientBalanceMessage = $(
                    "#message-insufficient-balance"
                );
                let cashChangeWallet = $(".cash-change-wallet");
                if (
                    parseFloat($(".customer-wallet-balance").val()) <
                    parseFloat($(".total-amount").val())
                ) {
                    insufficientBalanceMessage.text(
                        insufficientBalanceMessage.data("text")
                    );
                }
                cashChangeWallet.show();
                cashChangeWallet.removeClass("d-none").show();
            }
        }
    });

    $(".option-buttons input").trigger("change");

    $(".pos-paid-amount-element")
        .on("keypress", function (event) {
            let charCode = event.which || event.keyCode;
            let inputValue = $(this).val();

            if ((charCode < 48 || charCode > 57) && charCode !== 46) {
                event.preventDefault();
            }

            if (charCode === 46 && inputValue.includes(".")) {
                event.preventDefault();
            }
        })
        .on("input", function () {
            let minimumAmount = parseFloat($(this).attr("min")) || 0;
            let GivenAmount = parseFloat($(this).val()) || 0;
            let currencyPosition = $(this).data("currency-position");
            let currencySymbol = $(this).data("currency-symbol");
            let decimalPoint = $('#get-decimal-point').data('decimal-point')

            if (GivenAmount < minimumAmount) {
                $("#submit_order").prop("disabled", true);
            } else {
                $("#submit_order").prop("disabled", false);
            }

            let amount = Number(GivenAmount - minimumAmount).toFixed(decimalPoint);
            let result = "";

            if (currencyPosition?.toString() === "left") {
                result = currencySymbol + amount;
            } else {
                result = amount + currencySymbol;
            }

            $(".pos-change-amount-element").text(result);
        });
}

basicFunctionalityForCartSummary();
posUpdateQuantityFunctionality();

function checkedPaidAmount() {
    let paidAmount = $(".pos-paid-amount-element");
    if ($(".paid-by-cash").prop("checked") && paidAmount.val() === "") {
        toastMagic.error($("#message-enter-valid-amount").data("text"));
        return false;
    } else if (
        $(".paid-by-cash").prop("checked") &&
        parseFloat(paidAmount.val()) < parseFloat(paidAmount.attr("min"))
    ) {
        toastMagic.error($("#message-less-than-total-amount").data("text"));
        return false;
    }
    return true;
}

$(".action-coupon-discount").on("click", function (event) {
    let couponCode = $("#coupon_code").val();
    if (couponCode.length === 0) {
        toastMagic.error($(this).data("error-message"));
        event.preventDefault();
    } else {
        $.ajaxSetup({
            headers: {
                "X-CSRF-TOKEN": $('meta[name="_token"]').attr("content"),
            },
        });
        $.post({
            url: $("#route-admin-pos-coupon-discount").data("url"),
            data: {
                coupon_code: couponCode,
            },
            beforeSend: function () {
                $("#loading").fadeIn();
            },
            success: function (data) {
                if (data.coupon === "success") {
                    toastMagic.success(
                        $("#message-coupon-added-successfully").data("text"), '',
                        {
                            CloseButton: true,
                            ProgressBar: true,
                        }
                    );
                } else if (data.coupon === "amount_low") {
                    toastMagic.warning($("#message-this-discount-is-not-applied-for-this-amount").data("text"));
                } else if (data.coupon === "cart_empty") {
                    toastMagic.warning($("#message-cart-is-empty").data("text"));
                } else if (data.cart === "empty") {
                    toastMagic.warning($("#message-please-add-product-in-cart-before-applying-coupon").data("text"));
                } else {
                    toastMagic.warning($("#message-coupon-is-invalid").data("text"));
                }
                $("#add-coupon-discount").modal("hide");
                $("#cart").empty().html(data.view);
                reinitializeTooltips();
                basicFunctionalityForCartSummary();
                posUpdateQuantityFunctionality();
                viewAllHoldOrders("keyup");
                removeFromCart();
                $("#search").focus();
            },
            complete: function () {
                $(".modal-backdrop").addClass("d-none");
                $(".footer-offset").removeClass("modal-open");
                $("#loading").fadeOut();
            },
        });
    }
});

$(".action-extra-discount").on("click", function (event) {
    let discount = $("#dis_amount").val();
    let type = $("#type_ext_dis").val();
    if (discount.length === 0) {
        toastMagic.error($(this).data("error-message"));
        event.preventDefault();
    } else if (discount > 0) {
        $.ajaxSetup({
            headers: {
                "X-CSRF-TOKEN": $('meta[name="_token"]').attr("content"),
            },
        });
        $.post({
            url: $("#route-admin-pos-update-discount").data("url"),
            data: {
                discount: discount,
                type: type,
            },
            beforeSend: function () {
                $("#loading").fadeIn();
            },
            success: function (data) {
                if (data.extraDiscount === "success") {
                    toastMagic.success(
                        $("#message-extra-discount-added-successfully").data(
                            "text"
                        ), '',
                        {
                            CloseButton: true,
                            ProgressBar: true,
                        }
                    );
                }else if((data.cart === "empty")){
                     toastMagic.warning(
                        $("#message-please-add-product-in-cart-before-applying-discount").data("text"), '',
                        {
                            CloseButton: true,
                            ProgressBar: true,
                        }
                    );
                }
                 else if (data.extraDiscount === "empty") {
                    toastMagic.warning(
                        $("#message-cart-is-empty").data("text"), '',
                        {
                            CloseButton: true,
                            ProgressBar: true,
                        }
                    );
                } else {
                    toastMagic.warning($("#message-this-discount-is-not-applied-for-this-amount").data("text"));
                }
                $("#add-discount").modal("hide");
                $(".modal-backdrop").addClass("d-none");
                $("#cart").empty().html(data.view);
                reinitializeTooltips();
                basicFunctionalityForCartSummary();
                posUpdateQuantityFunctionality();
                removeFromCart();
                $("#search").focus();
            },
            complete: function () {
                $(".modal-backdrop").addClass("d-none");
                $(".footer-offset").removeClass("modal-open");
                $("#loading").fadeOut();
            },
        });
    } else {
        toastMagic.warning($("#message-amount-can-not-be-negative-or-zero").data("text"));
    }
});

function posUpdateQuantityFunctionality() {
    $(".action-pos-update-quantity").on("change", function (event) {
        let getKey = $(this).data("product-key");
        let quantity = $(this).val();
        let variant = $(this).data("product-variant");
        getPOSUpdateQuantity(getKey, quantity, event, variant);
    });
}

document.addEventListener('input', function(event) {
    if (event.target.classList.contains('action-pos-update-quantity')) {
        sanitizeAndValidateQuantityInput(event.target);
    }
});
function getPOSUpdateQuantity(key, qty, e, variant = null) {
    if (qty !== "") {
        $.post(
            $("#route-admin-pos-update-quantity").data("url"),
            {
                _token: $('meta[name="_token"]').attr("content"),
                key: key,
                quantity: qty,
                variant: variant,
            },
            function (data) {
                updateQuantityResponseProcess(data);
            }
        );
    } else {
        let element = $(e.target);
        let minValue = parseInt(element.attr("min"));
        $.post(
            $("#route-admin-pos-update-quantity").data("url"),
            {
                _token: $('meta[name="_token"]').attr("content"),
                key: key,
                quantity: minValue,
                variant: variant,
            },
            function (data) {
                updateQuantityResponseProcess(data);
            }
        );
    }

    if (e.type == "keydown") {
        if (
            $.inArray(e.keyCode, [46, 8, 9, 27, 13, 190]) !== -1 ||
            (e.keyCode == 65 && e.ctrlKey === true) ||
            (e.keyCode >= 35 && e.keyCode <= 39)
        ) {
            return;
        }
        if (
            (e.shiftKey || e.keyCode < 48 || e.keyCode > 57) &&
            (e.keyCode < 96 || e.keyCode > 105)
        ) {
            e.preventDefault();
        }
    }
}

function updateQuantityResponseProcess(data) {
    if (data.productType === "physical" && data.qty < 0) {
        toastMagic.warning($("#message-product-quantity-is-not-enough").data("text"));
    }
    if (data.upQty === "zeroNegative") {
        toastMagic.warning($("#message-product-quantity-cannot-be-zero-in-cart").data("text"));
    }
    if (data.quantityUpdate == 1) {
        toastMagic.success(
            $("#message-product-quantity-updated").data("text"), '',
            {
                CloseButton: true,
                ProgressBar: true,
            }
        );
    }
    $("#cart").empty().html(data.view);
    reinitializeTooltips();
    posUpdateQuantityFunctionality();
    viewAllHoldOrders("keyup");
    removeFromCart();
}

let dropdownSelect = $("#dropdown-order-select");
dropdownSelect.on(
    "click",
    ".dropdown-menu .dropdown-item:not(:last-child)",
    function () {
        let selectedText = $(this).text();
        dropdownSelect.find(".dropdown-toggle").text(selectedText);
    }
);

$("#order-place").submit(function (eventObj) {
    eventObj.preventDefault();
    let customerValue = $("#customer").val();
    if (customerValue) {
        $(this).append(
            '<input type="hidden" name="user_id" value="' +
            customerValue +
            '" /> '
        );
    }
    return true;
});

$(function () {
    $(document).on("click", "input[type=number]", function () {
        this.select();
    });
});

window.addEventListener("click", function (event) {
    let searchResultBoxes =
        document.getElementsByClassName("search-result-box");
    for (let i = 0; i < searchResultBoxes.length; i++) {
        let searchResultBox = searchResultBoxes[i];
        if (
            event.target !== searchResultBox &&
            !searchResultBox.contains(event.target)
        ) {
            searchResultBox.style.display = "none";
        }
    }
});

function renderViewHoldOrdersFunctionality() {
    $(".action-cancel-customer-order").on("click", function () {
        $.ajaxSetup({
            headers: {
                "X-CSRF-TOKEN": $('meta[name="_token"]').attr("content"),
            },
        });
        $.post({
            url: $("#route-admin-pos-cancel-order").data("url"),
            data: {
                cart_id: $(this).data("cart-id"),
            },
            beforeSend: function () {
                $("#loading").fadeIn();
            },
            success: function (data) {
                $("#hold-orders-modal-content").empty().html(data.view);
                renderViewHoldOrdersFunctionality();
                toastMagic.info(data.message);
                location.reload();
            },
            complete: function () {
                $("#loading").fadeOut();
            },
        });
    });
}

$(".action-print-pos-invoice").on("click", function () {
    const divName = $(this).data("print");
    printSpecificSectionWithPrintArea(divName);
});

function printSpecificSectionWithPrintArea(selector) {
    try {
        $(selector).printThis();
    } catch (e) {
        console.error("Printing failed:", e);
    }
}

const renderRippleEffect = () => {
    function createRipple(event) {
        const button = event.currentTarget;
        const circle = document.createElement("span");
        const diameter = Math.max(button.clientWidth, button.clientHeight);
        const radius = diameter / 2;
        circle.style.width = circle.style.height = `${diameter}px`;
        circle.classList.add("ripple");
        const ripple = button.getElementsByClassName("ripple")[0];
        if (ripple) {
            ripple.remove();
        }
        button.appendChild(circle);
    }
    const buttons = document.getElementsByClassName("btn-number");
    for (const button of buttons) {
        button.addEventListener("click", createRipple);
    }
};

function quickView(product_id) {
    $.ajax({
        url: $("#route-admin-pos-quick-view").data("url"),
        type: "GET",
        data: {
            product_id: product_id,
        },
        dataType: "json",
        beforeSend: function () {
            $("#loading").fadeIn();
        },
        success: function (data) {
            $("#quick-view-modal").empty().html(data.view);
            initSliderWithZoom();
            renderSelectProduct();
            renderRippleEffect();
            closeAlertMessage();
            $("#quick-view").modal("show");
        },
        complete: function () {
            $("#loading").fadeOut();
        },
    });
}

function getVariantForAlreadyInCart(event = null) {
    let current_val = parseFloat($(".in-cart-quantity-field").val());
    if (current_val > 0) {
        $(".in-cart-quantity-minus").removeAttr("disabled");
        if (event == "plus") {
            $(".in-cart-quantity-field").val(current_val + 1);
        } else {
            $(".in-cart-quantity-field").val(current_val - 1);
            if (current_val <= 2) {
                $(".in-cart-quantity-minus").attr("disabled", true);
            }
        }
    } else {
        $(".in-cart-quantity-minus").attr("disabled", true);
    }
    getVariantPrice("already_in_cart");
}

function checkAddToCartValidity() {
    var names = {};
    $("#add-to-cart-form input:radio").each(function () {
        names[$(this).attr("name")] = true;
    });
    var count = 0;
    $.each(names, function () {
        count++;
    });

    if ($("input:radio:checked").length - 1 == count) {
        return true;
    }
    return false;
}

function cartQuantityInitialize() {
    $(".btn-number").click(function (e) {
        e.preventDefault();
        let fieldName = $(this).attr("data-field");
        let type = $(this).attr("data-type");
        let input = $("input[name='" + fieldName + "']");
        let currentVal = parseInt(input.val());

        if (!isNaN(currentVal)) {
            if (type == "minus") {
                if (currentVal > input.attr("min")) {
                    input.val(currentVal - 1).change();
                }
                if (parseInt(input.val()) == input.attr("min")) {
                    $(this).attr("disabled", true);
                }
            } else if (type == "plus") {
                if (currentVal < input.attr("max")) {
                    input.val(currentVal + 1).change();
                }
                if (parseInt(input.val()) == input.attr("max")) {
                    $(this).attr("disabled", true);
                }
            }
        } else {
            input.val(0);
        }
    });

    $(".input-number").focusin(function () {
        $(this).data("oldValue", $(this).val());
    });

    $(".input-number").change(function () {
        sanitizeAndValidateQuantityInput(this);
        let minValue = parseInt($(this).attr("min"));
        let maxValue = parseInt($(this).attr("max"));
        let valueCurrent = parseInt($(this).val());
        let name = $(this).attr("name");
        if (valueCurrent >= minValue) {
            $(
                ".btn-number[data-type='minus'][data-field='" + name + "']"
            ).removeAttr("disabled");
        } else {
            sanitizeAndValidateQuantityInput(this);
            $(this).val($(this).data("oldValue"));
        }
        if (valueCurrent <= maxValue) {
            $(
                ".btn-number[data-type='plus'][data-field='" + name + "']"
            ).removeAttr("disabled");
        } else {
            $(this).val($(this).data("oldValue"));
        }
    });
     $(".cart-qty-field").on('change',function(){
        sanitizeAndValidateQuantityInput(this);
    });
    $(".input-number").keydown(function (e) {
        if (
            $.inArray(e.keyCode, [46, 8, 9, 27, 13, 190]) !== -1 ||
            (e.keyCode == 65 && e.ctrlKey === true) ||
            (e.keyCode >= 35 && e.keyCode <= 39)
        ) {
            return;
        }
        if (
            (e.shiftKey || e.keyCode < 48 || e.keyCode > 57) &&
            (e.keyCode < 96 || e.keyCode > 105)
        ) {
            e.preventDefault();
        }

        sanitizeAndValidateQuantityInput(this);
    });
}
function sanitizeAndValidateQuantityInput(inputElement) {
    inputElement.value = inputElement.value.replace(/[^0-9]/g, '').replace(/^0+/, '');
    const min = parseInt(inputElement.getAttribute("min")) || 1;
    const max = parseInt(inputElement.getAttribute("max")) || 100;
    const val = parseInt(inputElement.value);

    if (inputElement.value !== '' && (val < min || val > max)) {
        inputElement.value = min;
    }
}
function updateProductDetailsTopSection(response) {
    let formSelector = ".add-to-cart-details-form";
    $(formSelector)
        .find(".discounted-unit-price")
        .html(response?.discounted_unit_price);
    $(formSelector)
        .find(".product-details-chosen-price-amount")
        .html(response?.price);
    $(formSelector)
        .find(".product-total-unit-price")
        .html(response?.discount_amount > 0 ? response?.total_unit_price : "");

    if (response?.discount_amount > 0) {
        if (response?.discount_type === "flat") {
            $(formSelector)
                .find(".discounted_badge")
                .html(`${response?.discount}`);
        } else {
            $(formSelector)
                .find(".discounted_badge")
                .html(`- ${response?.discount}`);
        }
        $(formSelector).find(".discounted-badge-element").removeClass("d-none");
    } else {
        $(formSelector).find(".discounted-badge-element").addClass("d-none");
    }
}

function getVariantPrice(type = null) {
    if (
        $("#add-to-cart-form input[name=quantity]").val() > 0 &&
        checkAddToCartValidity()
    ) {
        $.ajaxSetup({
            headers: {
                "X-CSRF-TOKEN": $('meta[name="_token"]').attr("content"),
            },
        });
        $.ajax({
            type: "POST",
            url:
                $("#route-admin-pos-get-variant-price").data("url") +
                (type ? "?type=" + type : ""),
            data: $("#add-to-cart-form").serializeArray(),
            success: function (response) {
                updateProductDetailsTopSection(response);
                let price;
                let tax;
                let discount;
                stockStatus(
                    response.quantity,
                    "cart-qty-field-plus",
                    "cart-qty-field"
                );
                if (response.inCartStatus == 0) {
                    $(".default-quantity-system").removeClass("d-none");
                    $(".quick-view-modal-add-cart-button").text(
                        $("#message-add-to-cart").data("text")
                    );
                    $(".in-cart-quantity-system").addClass("d--none");
                    $(".default-quantity-system").removeClass("d--none");
                    price = response.price;
                    tax = response.tax;
                    discount = response.discount * response.requestQuantity;
                } else {
                    $(".default-quantity-system").addClass("d--none");
                    $(".in-cart-quantity-system").removeClass("d--none");
                    $(".quick-view-modal-add-cart-button").text(
                        $("#message-update-to-cart").data("text")
                    );
                    if (type == null) {
                        $(".in-cart-quantity-field").val(
                            response.inCartData.quantity
                        );
                        response.inCartData.quantity == 1
                            ? buttonDisableOrEnableFunction(
                                "in-cart-quantity-minus",
                                true
                            )
                            : "";
                        price = response.inCartData.price;
                        tax = response.inCartData.tax;
                        discount =
                            response.inCartData.discount *
                            response.inCartData.quantity;
                    } else {
                        price = response.price;
                        tax = response.tax;
                        discount = response.discount * response.requestQuantity;
                    }
                    stockStatus(
                        response.quantity,
                        "in-cart-quantity-plus",
                        "in-cart-quantity-field"
                    );
                }
                setProductData(
                    "add-to-cart-details-form",
                    response.price,
                    tax,
                    response.discount_text
                );
            },
        });
    }
}

function addToCart(form_id = "add-to-cart-form") {
    if (checkAddToCartValidity()) {
        $.ajaxSetup({
            headers: {
                "X-CSRF-TOKEN": $('meta[name="_token"]').attr("content"),
            },
        });
        $.post({
            url: $("#route-admin-pos-add-to-cart").data("url"),
            data: $("#" + form_id).serializeArray(),
            beforeSend: function () {
                $("#loading").fadeIn();
            },
            success: function (data) {
                if (data.data == 1) {
                    $("#cart-summary").empty().html(data.view);
                    reinitializeTooltips();
                    toastMagic.success(
                        $("#message-cart-updated").data("text"), '',
                        {
                            CloseButton: true,
                            ProgressBar: true,
                        }
                    );
                    data.inCartData && data.inCartData == 1
                        ? $(".in-cart-quantity-field").val(data.requestQuantity)
                        : "";
                    removeFromCart();
                    basicFunctionalityForCartSummary();
                    posUpdateQuantityFunctionality();
                    return false;
                } else if (data.data == 0) {
                    $(".product-stock-message")
                        .empty()
                        .html(
                            $("#get-product-stock-message").data("out-of-stock")
                        );
                    $(".pos-alert-message").removeClass("d-none");
                    return false;
                } else if (data.data == 'custom-error') {
                    Swal.fire({
                        icon: "error",
                        title: data?.title ?? $("#message-cart-word").data("text"),
                        text: data?.text ?? $("#message-sorry-product-is-out-of-stock").data(
                            "text"
                        ),
                    });
                    return false;
                } else {
                    $(".in-cart-quantity-field").val(data.quantity);
                    getVariantPrice();
                    setTimeout(function () {
                        $(".cart-qty-field").val(1);
                    }, 500);
                }
                $(".close-quick-view-modal").click();

                toastMagic.success(
                    $("#message-item-has-been-added-in-your-cart").data("text"), '',
                    {
                        CloseButton: true,
                        ProgressBar: true,
                    }
                );
                $("#cart").empty().html(data.view);
                reinitializeTooltips();
                viewAllHoldOrders("keyup");
                $(".search-result-box").empty().hide();
                $("#search").val("");
                basicFunctionalityForCartSummary();
                posUpdateQuantityFunctionality();
                removeFromCart();
            },
            complete: function () {
                $("#loading").fadeOut();
            },
        });
    } else {
        Swal.fire({
            type: "info",
            title: $("#message-cart-word").data("text"),
            text: $("#message-please-choose-all-the-options").data("text"),
        });
    }
}
function removeFromCart() {
    $(".remove-from-cart").on("click", function () {
        let id = $(this).data("id");
        let variant = $(this).data("variant");
        $.post(
            $("#route-admin-pos-remove-cart").data("url"),
            {
                _token: $('meta[name="_token"]').attr("content"),
                id: id,
                variant: variant,
            },
            function (data) {
                $("#cart").empty().html(data.view);
                reinitializeTooltips();
                if (data.errors) {
                    for (let index = 0; index < data.errors.length; index++) {
                        setTimeout(() => {
                            toastMagic.error(data.errors[index].message);
                        }, index * 500);
                    }
                } else {
                    toastMagic.info($("#message-item-has-been-removed-from-cart").data("text"));
                    viewAllHoldOrders("keyup");
                }
                posUpdateQuantityFunctionality();
                posUpdateQuantityFunctionality();
                removeFromCart();
            }
        );
    });
}
removeFromCart();

$(".js-example-matcher").select2({
    matcher: matchCustom,
});

function matchCustom(params, data) {
    if ($.trim(params.term) === "") {
        return data;
    }
    if (typeof data.text === "undefined") {
        return null;
    }

    if (data.text.toLowerCase().indexOf(params.term.toLowerCase()) > -1) {
        let modifiedData = $.extend({}, data, true);
        return modifiedData;
    }
    return null;
}

function closeAlertMessage() {
    $(".close-alert-message").on("click", function () {
        $(".pos-alert-message").addClass("d-none");
    });
}

function productStockMessage(type) {
    $(".product-stock-message")
        .empty()
        .html($("#get-product-stock-message").data(type));
    $(".pos-alert-message").removeClass("d-none");
}
function stockStatus(
    quantity,
    buttonDisableOrEnableClassName,
    inputQuantityClassName
) {
    let stockOutMessage = $("#message-stock-out").data("text");
    let stockInMessage = $("#message-stock-id").data("text");
    let elementStockStatusInQuickView = $(".stock-status-in-quick-view");
    let inputQuantity = $("." + inputQuantityClassName);
    if (quantity <= 0) {
        elementStockStatusInQuickView
            .removeClass("text-success")
            .addClass("text-danger");
        elementStockStatusInQuickView.html(
            `<i class="tio-checkmark-circle-outlined"></i> ` + stockOutMessage
        );
        productStockMessage("out-of-stock");
        buttonDisableOrEnableFunction(buttonDisableOrEnableClassName, true);
        inputQuantity.val(1);
        $(".btn-number[data-type='minus']").attr("disabled", true);
    } else if (inputQuantity.val() >= quantity) {
        productStockMessage("limited-stock");
        buttonDisableOrEnableFunction(buttonDisableOrEnableClassName, true);
        inputQuantity.val(quantity);
    } else {
        $(".pos-alert-message").addClass("d-none");
        elementStockStatusInQuickView
            .removeClass("text-danger")
            .addClass("text-success");
        elementStockStatusInQuickView.html(
            `<i class="tio-checkmark-circle-outlined"></i> ` + stockInMessage
        );
        buttonDisableOrEnableFunction(buttonDisableOrEnableClassName, false);
    }
}


if ($('.set-discount-amount').text().trim().length > 0) {
    $('.set-discount-wrapper').show();
} else {
    $('.set-discount-wrapper').hide();
}
function setProductData(parentClass, price, tax, discount) {
    let updatedTax = tax.replace(/[^\d.,]/g, "");
    if (updatedTax <= 0) {
        $(".tax-container").empty();
    }
    $("." + parentClass + " " + ".set-product-tax").html(tax);
    $("." + parentClass + " " + ".set-discount-amount").html(discount);
}
$(".close-alert--message-for-pos").on("click", function () {
    $(".alert--message-for-pos").removeClass("active");
});

// ---- swipper slider and zoom
function initSliderWithZoom() {
    $(".easyzoom").each(function () {
        $(this).easyZoom();
    });

    new Swiper(".quickviewSlider2", {
        slidesPerView: 1,
        spaceBetween: 10,
        loop: false,
        thumbs: {
            swiper: new Swiper(".quickviewSliderThumb2", {
                spaceBetween: 10,
                slidesPerView: 'auto',
                watchSlidesProgress: true,
                navigation: {
                    nextEl: ".swiper-quickview-button-next",
                    prevEl: ".swiper-quickview-button-prev",
                },
            }),
        },
    });
}

