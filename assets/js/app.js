(function() {
  var elements = document.querySelectorAll("[data-tw-bind]"),
  scope = {};
  elements.forEach(function(element) {
    //execute scope setter
    if (
      element.type === "text" ||
      element.type === "textarea" ||
      element.type === "select"
    ) {
      var propToBind = element.getAttribute("data-tw-bind");
      addScopeProp(propToBind);
      element.onkeyup = function() {
        scope[propToBind] = element.value;
      };
    }

    //bind prop to elements
    function addScopeProp(prop) {
      //add property if needed
      if (!scope.hasOwnProperty(prop)) {
        //value to populate with newvalue
        var value;
        Object.defineProperty(scope, prop, {
          set: function(newValue) {
            value = newValue;
            elements.forEach(function(element) {
              //change value to binded elements
              if (element.getAttribute("data-tw-bind") === prop) {
                if (
                  element.type &&
                  (element.type === "text" || element.type === "textarea")
                ) {
                  element.value = newValue;
                } else if (!element.type) {
                  element.innerHTML = newValue;
                }
              }
            });
          },
          get: function() {
            return value;
          },
          enumerable: true
        });
      }
    }
  });

  window.addEventListener(
    "load",
    function() {
      /* --------------------------------------------------------------------------
    | Disabling form submissions if there are invalid fields
    */
      // Fetch all the forms we want to apply custom Bootstrap validation styles to
      var forms = document.getElementsByClassName("needs-validation");
      // Loop over them and prevent submission
      var validation = Array.prototype.filter.call(forms, function(form) {
        form.addEventListener(
          "submit",
          function(event) {
            if (form.checkValidity() === false) {
              event.preventDefault();
              event.stopPropagation();
            }
            form.classList.add("was-validated");
          },
          false
        );
      });

      /* --------------------------------------------------------------------------
    | Toggling side bar
    */
      $("#menu-toggle").click(function(e) {
        e.preventDefault();
        $("#wrapper").toggleClass("toggled");
      });

      /* --------------------------------------------------------------------------
    | Launch BS modal via attribute
    */
      var triggerModal = $("[data-trigger-modal]").attr("data-trigger-modal");
      $(triggerModal).modal("show");

      $("body").tooltip({ selector: "[data-toggle=tooltip]" });

      /* --------------------------------------------------------------------------
    | Launch BS tab via attribute
    */
      var triggerModal = $("[data-trigger-tab]").attr("data-trigger-tab");
      $('a[href="' + triggerModal + '"]').tab("show");

      /* --------------------------------------------------------------------------
    | BS tab redirects
    */
      $('body a[data-toggle="tab"]').on("click", function(e) {
        // update url hash for redirect
        document.location.hash = $(this).attr("href");
      });
      var uriHash = $(location).prop("hash");
      if (uriHash.startsWith("#tab-")) {
        $('body a[href="' + uriHash + '"]').tab("show");
      }

        // Inline editing
        $(".inline-content").click(function (e) {
            e.preventDefault();
            $(this).hide();
            $(this).closest('.inline-editor').find('.inline-field').show().focus();
        });
        $('.inline-field').blur(function () {
            $(this).hide();
            $(this).closest('.inline-editor').find('.inline-content').show();
        });

      /* --------------------------------------------------------------------------
    | BS Checkable Table Rows
    */
      $("table.table-checkable tr.check")
        .find('input[type="checkbox"]:checked')
        .each(function() {
          $(this)
            .closest("tr")
            .toggleClass("bg-secondary text-white");
        });
      $("table.table-checkable tr.check").click(function(event) {
        if (event.target.type !== "checkbox") {
          $(":checkbox", this).trigger("click");
          $(this).toggleClass("bg-secondary text-white");
          var tCheckedNum = $(
            'table.table-checkable input[type="checkbox"]:checked'
          ).length;
          $(this)
            .closest("tr")
            .closest("table")
            .find(".checked-num")
            .html(tCheckedNum);
        }
      });
      $('table.table-checkable input[type="reset"]').click(function(event) {
        $(this)
          .closest("table")
          .find(".checked-num")
          .html(0);
        $(this)
          .closest("table")
          .find("tr")
          .removeClass("bg-secondary text-white");
      });
      $('table.table-checkable button[type="reset"]').click(function(event) {
        $(this)
          .closest("table")
          .find(".checked-num")
          .html(0);
        $(this)
          .closest("table")
          .find("tr")
          .removeClass("bg-secondary text-white");
      });

      //select all checkboxes
      $('input[name="select_all"]').change(function() {
        //"select all" change
        var status = this.checked; // "select all" checked status
        $('input[name="selected[]"]').each(function() {
          //iterate all listed checkbox items
          this.checked = status; //change ".checkbox" checked status
        });
        showCheckedCount($(this).closest("table"));
      });

      $('input[name="selected[]"]').change(function() {
        //".checkbox" change
        //uncheck "select all", if one of the listed checkbox item is unchecked
        if (this.checked == false) {
          //if this item is unchecked
          $('input[name="select_all"]')[0].checked = false; //change "select all" checked status to false
        }

        //check "select all" if all checkbox items are checked
        if (
          $('input[name="selected[]"]:checked').length ==
          $('input[name="selected[]"]').length
        ) {
          $('input[name="select_all"]')[0].checked = true; //change "select all" checked status to true
        }
        showCheckedCount($(this).closest("table"));
      });

      function showCheckedCount(table) {
        var count = table.find('input[name="selected[]"]:checked').length;
        var container = table.closest('.table-container').find('.table-selected');
        container.find(".checked-num").html(count);
          
        if (container.hasClass('toggle')) {
          if (count == 0) {
            container.find(".table-selected-delete").attr("disabled", 'disabled');
            container.hide();
          } else {
            container.show();
            container.find(".table-selected-delete").removeAttr("disabled");
          }
        }
      }

      // Uncheck all checked checkboxes
      $(".table-container").find(".uncheck-selected").click(function(e){
        e.preventDefault();
        container = $(this).closest(".table-container");
        container.find('input[name="selected[]"]').prop("checked", false);
        container.find('input[name="select_all"]').prop("checked", false);
        showCheckedCount(container);
      });
    },
    false
  );
})();
