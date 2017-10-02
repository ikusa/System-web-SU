@extends('layouts.master')

@section('scripts')
   <script type="text/javascript">
   $(document).ready(function () {

      disableSubmit();

      $('#banyakPeserta').change(function () {
         changeBgColor(this.id, YELLOWISH);
         hideTooltip();
      });

      $('#tambah').click(function() {
         addRow($('#banyakPeserta').val());
      });

      // Change input field bg color when input changed to prevent mistake
      // when submiting after checking.
      // Reminder to self: Bind event handler to document if element isn't
      // created yet when document is ready.
      // Use .val() for form element and .text() for other html element.
      $(document).on("change", ".input-nim", function() {
        disableSubmit();
        $(this).css("background-color", YELLOWISH)
        .parent().next().text("[ NIM changed ]")
        .next().text("");

        if ($(this).val() == "") {
          $(this).parent().next().text("[ Empty NIM ]")
          .next().text("");
        }
      });

      // Remove the row based on row number stored in the button.
      // Need better alternative for removing parent node. -> done.
      $(document).on("click", ".btn-removal", function () {
        $(this).parent().parent().remove();
        existingRow -= 1;
      });

      // Will be converted to JQuery later?
      // Event listener for keeping value and clearing previous value
      // in input tambah when typing.
      banyakPeserta.addEventListener('focus', function () {
        banyakPeserta.setAttribute('data-value', this.value);
        this.value = '';
      });
      banyakPeserta.addEventListener('blur', function () {
        if (this.value === '')
          this.value = this.getAttribute('data-value');
      });

      // Store the csrf token meta from app.blade
      // And then putting it on the master.blade
      // Need to check later
      $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
      });
      // Send ajax request to check nim from input in table mahasiswa
      // and return necessary info.
      $('#check').click(function( event ) {
        // Still getting error 500 if using csrf on data as string
        var stolenToken = '{!! csrf_token() !!}';

        $.ajax({
          url: '/kelas/peserta/cek',
          type: 'post',
          data: $('input.checked').serialize(),
          dataType: 'json',

            success: function ( response ){
              //console.log(response);
              enableSubmit();
              // Variable for storing the actual amount of peserta that will be
              // enrolled to the class.
              existingPeserta = 0;

              $.each(response.result, function(k, v) {
                //console.log("K = "+k);
                //console.log(v);

                mahasiswa = response.result[k];
                nimInput = '#nim'+k; // Need better alternative

                if (mahasiswa.doesExist == true) {
                  var nama = mahasiswa.nama,
                      prodi = mahasiswa.program_studi,
                      id = mahasiswa.id;

                  existingPeserta += 1;
                  // Change input field background color to greenish color when found.
                  $(nimInput).css("background-color", GREENISH)
                      .prev().val(id)
                      .parent().next().text(nama)
                      .next().text(prodi);
                } else {
                  // Set pinkish color on input field when input wasn't found on the DB.
                  $(nimInput).css("background-color", PINKISH)
                    .parent().next().text("[ Student not found ]")
                    .next().text("");
                  disableSubmit();
                }
              });
            },
            error: function ( response ){
              // Do error handling later....
              console.log("Error while checking data.");
            }
         });
      });

      $('#daftarPeserta').on("submit", function (e) {
        e.preventDefault();

        var ids = $('input.submited').serialize();

        console.log('banyakPeserta='+existingPeserta+'&');
        console.log(ids);

        $.ajax({
          url: '/kelas/peserta/submit',
          type: 'post',
          data: 'banyakPeserta='+existingPeserta+'&'+ids,

          success: function () {
            console.log("Ye");
          },
          error: function () {
            console.log("No");
          }

        });

      });
  });

  // Need to check for each class later.
  MAX_PESERTA = 200;
  existingPeserta = 0;
  existingRow = 0;

  // Color
  var GREENISH = "#a9ff52",
      PINKISH = "#f5baf1",
      YELLOWISH = "#f6ff8b",
      REDDISH = "#eb4760";

  function disableSubmit() {
    $("#submitPeserta").prop({"disabled":true,"title":"Please check before submitting"});
  }

  function enableSubmit() {
    $("#submitPeserta").prop({"disabled":false,"title":""});

  }

  function changeBgColor(elementId, color) {
    $("#"+elementId).css("background-color", color);
  }

  function showTooltip(tooltipId) {
    $("#"+tooltipId).css({
      "visibility": "visible",
      "opacity": "1"
    });
  }

  function hideTooltip(tooltipId) {
    $("#"+tooltipId).css({
      "visibility": "hidden",
      "opacity": "0"
    });
  }

  // Add new rows in peserta table based on passed number.
  function addRow(banyak) {
    var html = [];
    var oldTotalPesertaBaru = $("#inputTambah").val();
    totalPesertaBaru = Number(oldTotalPesertaBaru) + Number(banyak);
    existingRow += Number(banyak);
    // Need different variable for storing peserta
    // totalPesertaBaru should be used for naming the input field only.
    // Right now using existingPeserta to store the actual amount.
    // And existingRow for storing the total input row.
    if (existingRow >= MAX_PESERTA) {
      alert("Input lebih dari batas maximal kelas.");
      changeBgColor("banyakPeserta", PINKISH);
    } else if (banyak > 0 && banyak <= 100) {
        changeBgColor("banyakPeserta", GREENISH);
        disableSubmit();
        $("#inputTambah").val(totalPesertaBaru);

        var count = Number(oldTotalPesertaBaru);
        for (; count < totalPesertaBaru; count++) {
          var index = count + 1;
          html.push("<tr id='", count, "'><td id='peserta", count, "'>"
              + "<input type='hidden' class='submited' value=''  id='idMhs", count, "' name='idMhs", count, "'>"
              + "<input class='form-control input-nim checked' tabindex='", index, "'"
              + "type='text' id='nim", count, "' name='nim", count, "'></td>"
              + "<td></td><td></td><td class='action-column'>"
              + "<button type='button' class='btn btn-info btn-removal'"
              + " value='", count, "'>&#x1F5D9</button></td></tr>");
        }
        $('#peserta').append(html.join(''));

      } else {
        showTooltip("tooltipTambah");
        changeBgColor("banyakPeserta", PINKISH);
      }
  }

</script>
@endsection

@section('content')

<style>
   /* Temp? fix to keep consistent column size when the table is empty. */
   th {
      padding-left: 8px !important;
      width: 25%;
      min-width: 25%;
      max-width: 30%;
      }
   td {
      vertical-align: middle !important;
      min-width: 25%;
      max-width: 30%;
      }

   .action-column {
      padding-left: 0px !important;
   }

   .btn-removal {
      background-color: #f993a3 !important;
      color: #000 !important;
      border-color: #000 !important;
      border-radius: 6px !important;
      border-width: thin !important;
      -webkit-transition-duration: 0.4s !important; /* Safari */
      transition-duration: 0.4s !important;
   }

   .btn-removal:hover {
       background-color: #ef425c !important; /* Green */
       color: white !important;
   }

   .form-control {
      width: 100%;
      padding: 10px;
      margin: 0px;
      box-sizing: border-box;
      -moz-box-sizing: border-box;
      -webkit-box-sizing: border-box;
   }

   #banyakPeserta {
      text-align:right;
      display: inline;
      width: 15em;
      margin-right: 6px;
   }

   /* Configuring the padding inside input number.
      Webkit for any webkit based browser.
   */
   input::-webkit-outer-spin-button,
   input::-webkit-inner-spin-button { margin-left: 10px;}

   /* IDK any other method to modify that dmn spin button on firefox. */
   input[type=number] {
      -moz-appearance: textfield;
   }

   button {
      margin-left: 5px;
      display: inline;
   }

   /* Create tooltip if input outside of range. */
   .tooltip {
         position: relative;
         bottom: 4px;
         width: 210px;
         height: 32px;
         line-height: 20px;
         padding: 6px;
         font-size: 14px;
         text-align: center;
         color: rgb(255, 255, 255);
         background: rgb(0, 0, 0);
         border: 0px solid rgb(255, 255, 255);
         border-radius: 8px;
         text-shadow: rgba(0, 0, 0, 0.1) 1px 1px 1px;
         box-shadow: rgba(0, 0, 0, 0.1) 1px 1px 2px 0px;
         opacity: 0;
         visibility: hidden;
         transition: opacity 1s;
   }

   /* Little triangle below tooltip. */
   .tooltip:after {
         content: "";
         position: absolute;
         width: 0;
         height: 0;
         border-width: 10px;
         border-style: solid;
         border-color: #000 transparent transparent transparent;
         top: 32px;
         left: 95px;
   }
</style>

<div class="container">
   <!-- page content -->
   <div class="right_col" role="main">
      <!-- top tiles -->

      <!-- /top tiles -->

      <div class="row">
         <div class="col-md-12 col-sm-12 col-xs-12">
            <div class="dashboard_graph">
               <div class="clearfix"></div>
            </div>
         </div>
      </div>
      <br>
      <form id="daftarPeserta" action="/kelas/peserta/submit" method="post">
         {{csrf_field()}}
      <span class="tooltip" id="tooltipTambah">Input between 1 - 100.</span>
      <input type="number" id="banyakPeserta" placeholder="Banyak peserta baru"
         value="1" max="100" min="1" class="form-control"/>

      <button type="button" class="btn btn-info" id="tambah">Tambah</button>
      <input type="hidden" value="0" name="inputTambah" id="inputTambah" class="checked">

      <button type="button" class="btn btn-info" id="check">Check</button>
      <div style="float:right;">
         <input type="hidden" id="idKelas" name='idKelas' value='{{$idKelas}}' class="form-control checked submited" >
         <input type="submit" id="submitPeserta" class="btn btn-success" value="Submit">
      </div>


      <div class="x_panel">
      <div class="x_content">
         <table class="table table-hover" id="peserta">
            <tbody>
            <th>NIM</th>
            <th>Nama</th>
            <th>Program Studi</th>
            <th>Aksi</th>
            @if ($table != null)
               @foreach ($table as $key)
                  <tr>
                     <td>{{$key->nim}}</td>
                     <td>{{$key->nama}}</td>
                     <td>{{$key->program_studi}}</td>
                  </tr>
               @endforeach
            @endif

            </tbody>
         </table>
      </form>
      </div>
      </div>


   </div>
</div>
</div>
<!-- /page content -->
</div>

@endsection
