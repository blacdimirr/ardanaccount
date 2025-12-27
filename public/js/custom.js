/**
 *
 * You can write your JS code here, DO NOT touch the default style file
 * because it will make it harder for you to update.
 *
 */

"use strict";

$(function() {
    if ($('.custom-scroll').length) {
        $(".custom-scroll").niceScroll();
        $(".custom-scroll-horizontal").niceScroll();
    }


    loadConfirm();
    bindFormSubmitGuard();
    initGlobalLoader();
    daterange();

});

$(document).ready(function() {
    if ($(".datatable").length > 0) {
        new simpleDatatables.DataTable(".datatable");
    }


    loadConfirm();
    bindFormSubmitGuard();
    select2();
    daterange();


});

function validation() {

    var forms = document.querySelectorAll('.needs-validation');

    Array.prototype.forEach.call(forms, function (form) {

        form.addEventListener('submit', function (event) {
            var submitButton = form.querySelector('button[type="submit"], input[type="submit"]');

            if (submitButton) {
                submitButton.disabled = true;
            }
            if (form.checkValidity() === false) {
                event.preventDefault();
                event.stopPropagation();
                if (submitButton) {
                    submitButton.disabled = false;
                }
            }

            form.classList.add('was-validated');
        }, false);
    });
}

$(document).ready(function () {
    if ($(".pc-dt-simple").length > 0) {
        $($(".pc-dt-simple")).each(function (index, element) {
            var id = $(element).attr('id');
            const dataTable = new simpleDatatables.DataTable("#" + id);
        });
    }

    if ($(".needs-validation").length > 0) {
        validation();
    }


    common_bind();
    summernote();


    // for Choose file
    $(document).on('change', 'input[type=file]', function () {
        var fileclass = $(this).attr('data-filename');
        var finalname = $(this).val().split('\\').pop();
        $('.' + fileclass).html(finalname);
    });
});

function daterange() {
    if ($("#pc-daterangepicker-1").length > 0) {
        document.querySelector("#pc-daterangepicker-1").flatpickr({
            mode: "range"
        });
    }
}

function select2() {
    if ($(".select2").length > 0) {
        $($(".select2")).each(function(index, element) {
            var id = $(element).attr('id');
            var multipleCancelButton = new Choices(element, {
                removeItemButton: true,
            });
        });

    }

}

// function select2() {
//     if ($(".select2").length > 0) {
//         $($(".select2")).each(function(index, element) {
//             var id = $(element).attr('id');
//             var multipleCancelButton = new Choices(
//                 '#' + id, {
//                     removeItemButton: true,
//                 }
//             );
//         });

//     }

// }

// // minimum setup
// (function () {
//     const d_week = new Datepicker(document.querySelector('.pc-datepicker-1'), {
//         buttonClass: 'btn',
//     });
// })();
// (function () {
//     const d_week = new Datepicker(document.querySelector('.pc-datepicker-1_modal'), {
//         buttonClass: 'btn',
//     });
// })();

function show_toastr(type, message) {
    var f = document.getElementById('liveToast');
    var a = new bootstrap.Toast(f).show();
    if (type == 'success') {
        $('#liveToast').addClass('bg-primary');
    } else {
        $('#liveToast').addClass('bg-danger');
    }
    $('#liveToast .toast-body').html(message);
}

$(document).on('click', 'a[data-ajax-popup="true"], button[data-ajax-popup="true"], div[data-ajax-popup="true"]', function() {

    var title1 = $(this).data("title");
    var title2 = $(this).data("bs-original-title");
    var title = (title1 != undefined) ? title1 : title2;
    var size = ($(this).data('size') == '') ? 'md' : $(this).data('size');
    var url = $(this).data('url');
    $("#commonModal .modal-title").html(title);
    $('#commonModal .modal-dialog').removeClass('modal-xl modal-sm modal-md modal-lg modal-xxl');
    $("#commonModal .modal-dialog").addClass('modal-' + size);
    $.ajax({
        url: url,
        success: function(data) {
            $('#commonModal .body').html(data);
            $("#commonModal").modal('show');
            // daterange_set();
            taskCheckbox();
            validation();
            common_bind("#commonModal");
            setTimeout(function () {
                commonLoader();
            }, 600);
            select2();
        },
        error: function(data) {
            data = data.responseJSON;
            show_toastr('Error', data.error, 'error')
        }
    });

});


function arrayToJson(form) {
    var data = $(form).serializeArray();
    var indexed_array = {};

    $.map(data, function(n, i) {
        indexed_array[n['name']] = n['value'];
    });

    return indexed_array;
}


function common_bind() {

}


function taskCheckbox() {
    var checked = 0;
    var count = 0;
    var percentage = 0;

    count = $("#check-list input[type=checkbox]").length;
    checked = $("#check-list input[type=checkbox]:checked").length;
    percentage = parseInt(((checked / count) * 100), 10);
    if (isNaN(percentage)) {
        percentage = 0;
    }
    $(".custom-label").text(percentage + "%");
    $('#taskProgress').css('width', percentage + '%');


    $('#taskProgress').removeClass('bg-warning');
    $('#taskProgress').removeClass('bg-primary');
    $('#taskProgress').removeClass('bg-success');
    $('#taskProgress').removeClass('bg-danger');

    if (percentage <= 15) {
        $('#taskProgress').addClass('bg-danger');
    } else if (percentage > 15 && percentage <= 33) {
        $('#taskProgress').addClass('bg-warning');
    } else if (percentage > 33 && percentage <= 70) {
        $('#taskProgress').addClass('bg-primary');
    } else {
        $('#taskProgress').addClass('bg-success');
    }
}


function commonLoader() {
    $('[data-bs-toggle="tooltip"]').tooltip();
    if ($('[data-toggle="tags"]').length > 0) {
        $('[data-toggle="tags"]').tagsinput({ tagClass: "badge badge-primary" });
    }


    var e = $(".scrollbar-inner");
    e.length && e.scrollbar().scrollLock()

    var e1 = $(".custom-input-file");
    e1.length && e1.each(function () {
        var e1 = $(this);
        e1.on("change", function (t) {
            ! function (e, t, a) {
                var n, o = e.next("label"),
                    i = o.html();
                t && t.files.length > 1 ? n = (t.getAttribute("data-multiple-caption") || "").replace("{count}", t.files.length) : a.target.value && (n = a.target.value.split("\\").pop()), n ? o.find("span").html(n) : o.html(i)
            }(e1, this, t)
        }), e1.on("focus", function () {
            ! function (e) {
                e.addClass("has-focus")
            }(e1)
        }).on("blur", function () {
            ! function (e) {
                e.removeClass("has-focus")
            }(e1)
        })
    })

    var e2 = $('[data-toggle="autosize"]');
    e2.length && autosize(e2);

    


    if ($(".jscolor").length) {
        jscolor.installByClassName("jscolor");
    }

    // for Choose file
    $(document).on('change', 'input[type=file]', function () {
        var fileclass = $(this).attr('data-filename');
        var finalname = $(this).val().split('\\').pop();
        $('.' + fileclass).html(finalname);
    });
}

summernote();
function summernote() {
    if ($(".summernote-simple").length) {
        $('.summernote-simple').summernote({
            dialogsInBody: true,
            minHeight: 200,
            toolbar: [
                ['style', ['style']],
                ["font", ["bold", "italic", "underline", "clear", "strikethrough"]],
                ['fontname', ['fontname']],
                ['color', ['color']],
                ["para", ["ul", "ol", "paragraph"]],
            ]
        });
    }
}


function loadConfirm() {
    $(document)
        .off("click.confirm", ".bs-pass-para")
        .on("click.confirm", ".bs-pass-para", function (event) {
            event.preventDefault();
            const $trigger = $(this);
            const form = $trigger.closest("form");
            const confirmMessage = $trigger.data("confirm");
            const confirmYes = $trigger.data("confirm-yes");
            let title = "¿Confirmar acción?";
            let text = "Esta acción no se puede deshacer. ¿Deseas continuar?";

            if (confirmMessage) {
                const parts = confirmMessage.toString().split("|");
                if (parts.length > 1) {
                    title = parts[0].trim() || title;
                    text = parts.slice(1).join("|").trim() || text;
                } else {
                    text = confirmMessage;
                }
            }

            const swalWithBootstrapButtons = Swal.mixin({
                customClass: {
                    confirmButton: "btn btn-success",
                    cancelButton: "btn btn-danger"
                },
                buttonsStyling: false
            });

            swalWithBootstrapButtons.fire({
                title: title,
                text: text,
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "Sí, continuar",
                cancelButtonText: "Cancelar",
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    if (confirmYes) {
                        new Function(confirmYes)();
                        return;
                    }

                    if (form.length) {
                        form.submit();
                    }
                }
            });
        });

}

function bindFormSubmitGuard() {
    $(document)
        .off("submit.form-guard", "form")
        .on("submit.form-guard", "form", function (event) {
            const form = this;
            const $form = $(form);
            const method = (form.getAttribute("method") || "get").toLowerCase();

            if ($form.data("disable-on-submit") === false) {
                return;
            }

            if ($form.data("submitting")) {
                event.preventDefault();
                return;
            }

            if (form.classList.contains("needs-validation") && form.checkValidity && !form.checkValidity()) {
                return;
            }

            const action = (form.getAttribute("action") || "").toLowerCase();
            const formLoadingText = $form.data("loading-text");
            const formLoadingMessage = $form.data("loading-message");
            const formLoadingFlag = $form.data("loading") === true || $form.data("loading") === "true";
            const isLongRunning =
                formLoadingFlag ||
                action.includes("report") ||
                action.includes("extracto") ||
                action.includes("pdf") ||
                action.includes("export");

            $form.data("submitting", true);
            $form.attr("aria-busy", "true");

            $form.find('button[type="submit"], input[type="submit"]').each(function () {
                const $button = $(this);
                const buttonLoadingText = $button.data("loading-text");
                const buttonLoadingFlag = $button.data("loading") === true || $button.data("loading") === "true";
                const shouldShowLoading = buttonLoadingFlag || !!buttonLoadingText || isLongRunning;

                if ($button.data("no-disable")) {
                    return;
                }

                if ($button.data("original-content") === undefined) {
                    $button.data("original-content", $button.is("input") ? $button.val() : $button.html());
                }

                $button.prop("disabled", true);

                if (shouldShowLoading) {
                    const loadingText = buttonLoadingText || formLoadingText || "Procesando...";
                    if ($button.is("input")) {
                        $button.val(loadingText);
                    } else {
                        $button.html(
                            '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>' +
                                loadingText
                        );
                    }
                }
            });

            if (isLongRunning) {
                showGlobalLoader(formLoadingMessage);
            }
        });
}

function initGlobalLoader() {
    $(document)
        .off("click.global-loader", "[data-loading='true']")
        .on("click.global-loader", "[data-loading='true']", function () {
            const $trigger = $(this);
            const tagName = this.tagName.toLowerCase();
            const isSubmitButton = tagName === "button" && ($trigger.attr("type") || "submit") === "submit";

            if (isSubmitButton && $trigger.closest("form").length) {
                return;
            }

            if ($trigger.data("loading-active")) {
                return;
            }

            $trigger.data("loading-active", true);

            if ($trigger.data("original-content") === undefined) {
                $trigger.data("original-content", $trigger.is("input") ? $trigger.val() : $trigger.html());
            }

            if ($trigger.is("button") || $trigger.is("a")) {
                $trigger.addClass("disabled").attr("aria-disabled", "true");
            }

            const buttonLoadingText = $trigger.data("loading-text");
            if (buttonLoadingText) {
                if ($trigger.is("input")) {
                    $trigger.val(buttonLoadingText);
                } else {
                    $trigger.html(
                        '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>' +
                            buttonLoadingText
                    );
                }
            }

            showGlobalLoader($trigger.data("loading-message"));
        });

    if (window.axios && window.axios.interceptors) {
        window.axios.interceptors.request.use(function (config) {
            const url = (config.url || "").toLowerCase();
            const explicit = config.headers && (config.headers["X-Long-Request"] || config.headers["X-Loading"]);
            const isLongRunning =
                explicit ||
                url.includes("report") ||
                url.includes("nomina") ||
                url.includes("extracto") ||
                url.includes("conciliacion") ||
                url.includes("dgii") ||
                url.includes("export") ||
                url.includes("pdf");

            if (isLongRunning) {
                config.metadata = config.metadata || {};
                config.metadata.loaderTimer = setTimeout(function () {
                    config.metadata.loaderShown = true;
                    showGlobalLoader();
                }, 500);
            }

            return config;
        });

        window.axios.interceptors.response.use(
            function (response) {
                finalizeAxiosLoader(response.config);
                return response;
            },
            function (error) {
                if (error && error.config) {
                    finalizeAxiosLoader(error.config);
                }
                showGlobalErrorMessage();
                return Promise.reject(error);
            }
        );
    }

    setupHtml2PdfLoader();
}

function finalizeAxiosLoader(config) {
    if (!config || !config.metadata) {
        return;
    }

    if (config.metadata.loaderTimer) {
        clearTimeout(config.metadata.loaderTimer);
    }

    if (config.metadata.loaderShown) {
        hideGlobalLoader();
    }
}

function showGlobalLoader(message) {
    const $overlay = $("#global-loading-overlay");
    if (!$overlay.length) {
        return;
    }

    const $message = $("#global-loading-message");
    const defaultMessage =
        "Estamos procesando su solicitud. Este proceso puede tardar unos segundos. Por favor, no cierre la página ni presione atrás.";

    if (message) {
        $message.text(message);
    } else {
        $message.text(defaultMessage);
    }

    const currentCount = $overlay.data("loading-count") || 0;
    $overlay.data("loading-count", currentCount + 1);
    $overlay.removeClass("d-none");
}

function hideGlobalLoader() {
    const $overlay = $("#global-loading-overlay");
    if (!$overlay.length) {
        return;
    }

    const currentCount = $overlay.data("loading-count") || 0;
    const nextCount = Math.max(currentCount - 1, 0);
    $overlay.data("loading-count", nextCount);

    if (nextCount === 0) {
        $overlay.addClass("d-none");
    }
}

function showGlobalErrorMessage() {
    if (typeof show_toastr === "function") {
        show_toastr("error", "Ocurrió un error al procesar la solicitud. Intente de nuevo.");
    }
}

function setupHtml2PdfLoader() {
    const maxAttempts = 20;
    let attempts = 0;
    const interval = setInterval(function () {
        attempts += 1;
        if (window.html2pdf && !window.html2pdf.__loaderWrapped) {
            const originalHtml2Pdf = window.html2pdf;
            window.html2pdf = function () {
                const instance = originalHtml2Pdf.apply(this, arguments);
                if (instance && typeof instance.save === "function") {
                    const originalSave = instance.save;
                    instance.save = function () {
                        showGlobalLoader();
                        const result = originalSave.apply(this, arguments);
                        if (result && typeof result.finally === "function") {
                            return result.finally(hideGlobalLoader);
                        }
                        hideGlobalLoader();
                        return result;
                    };
                }
                return instance;
            };
            window.html2pdf.__loaderWrapped = true;
            clearInterval(interval);
            return;
        }

        if (attempts >= maxAttempts) {
            clearInterval(interval);
        }
    }, 500);
}


function postAjax(url, data, cb) {
    var token = $('meta[name="csrf-token"]').attr('content');
    var jdata = { _token: token };

    for (var k in data) {
        jdata[k] = data[k];
    }

    $.ajax({
        type: 'POST',
        url: url,
        data: jdata,
        success: function(data) {
            if (typeof(data) === 'object') {
                cb(data);
            } else {
                cb(data);
            }
        },
    });
}

function deleteAjax(url, data, cb) {
    var token = $('meta[name="csrf-token"]').attr('content');
    var jdata = { _token: token };

    for (var k in data) {
        jdata[k] = data[k];
    }

    $.ajax({
        type: 'DELETE',
        url: url,
        data: jdata,
        success: function(data) {
            if (typeof(data) === 'object') {
                cb(data);
            } else {
                cb(data);
            }
        },
    });
}

$(document).on('click', '.fc-day-grid-event', function(e) {
    // if (!$(this).hasClass('project')) {
    e.preventDefault();
    var event = $(this);
    var title = $(this).find('.fc-content .fc-title').html();
    var size = 'md';
    var url = $(this).attr('href');
    $("#commonModal .modal-title").html(title);
    $('#commonModal .modal-dialog').removeClass('modal-xl modal-sm modal-md modal-lg modal-xxl');
    $("#commonModal .modal-dialog").addClass('modal-' + size);
    $.ajax({
        url: url,
        success: function(data) {
            $('#commonModal .modal-body').html(data);
            $("#commonModal").modal('show');
            common_bind();
            select2();
        },
        error: function(data) {
            data = data.responseJSON;
            toastrs('Error', data.error, 'error')
        }
    });
    // }
});

function JsSearchBox() {
    if ($(".js-searchBox").length)
    {
        $( ".js-searchBox" ).each(function( index ) {
            if($(this).parent().find('.formTextbox').length == 0)
            {
                $(this).searchBox({ elementWidth: ''});
            }
        });
    }
}   

$(document).on('click', 'a[data-ajax-popup-over="true"], button[data-ajax-popup-over="true"], div[data-ajax-popup-over="true"]', function () {

    var validate = $(this).attr('data-validate');
    var id = '';
    if (validate) {
        id = $(validate).val();
    }

    var title = $(this).data('title');
    var size = ($(this).data('size') == '') ? 'md' : $(this).data('size');
    var url = $(this).data('url');

    $("#commonModalOver .modal-title").html(title);
    $("#commonModalOver .modal-dialog").addClass('modal-' + size);

    $.ajax({
        url: url + '?id=' + id,
        success: function (data) {
            $('#commonModalOver .modal-body').html(data);
            $("#commonModalOver").modal('show');
            taskCheckbox();
            validation();
        },
        error: function (data) {
            data = data.responseJSON;
            show_toastr('Error', data.error, 'error')
        }
    });

});

$(document).ready(function() {
    $(document).on("change",".file-validate",function(){
        validate_file();
    });

    function validate_file() {
        let file_input = $('.file-validate')[0];
        let file_path = file_input.value;
        let max_size = file_size;
        let allowed_extensions = file_types;
        let file_error = $('.file-error');

        file_error.text('');

        if (file_input.files.length > 0) {
            let file = file_input.files[0];
            let file_size = file.size / 1024;
            let file_extension = file.name.split('.').pop().toLowerCase();
            let extensions_array = allowed_extensions.split(',');

            if (!extensions_array.includes(file_extension)) {
                file_error.text(type_err);
                file_input.value = '';
                return false;
            } else if (file_size > max_size) {
                file_error.text(size_err);
                file_input.value = '';
                return false;
            }
        }

        return true;
    }
});
