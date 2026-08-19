<div class="table-controls flex justify-between items-center mb-3">
        <div id="customSearchWrapper"></div>


    <div class="flex items-center gap-3">
        <div id="customLengthWrapper"></div>
    </div>
</div>

<script>
    $(document).ready(function() {

        // ✅ Click Event for Row Navigation (Excluding Buttons & Checkboxes)
        // $(document).on('click', '#clientTable tbody tr', function(e) {
        //     let $row = $(this);
        //     let link = $row.data('href');

        //     // Prevent redirection when clicking buttons, checkboxes, or links
        //     if (!$(e.target).closest('button, input[type="checkbox"], a').length) {
        //         //window.open(link, '_blank'); // Open link in a new tab
        //         window.location.href = link;
        //     }
        // });

        // Select/Deselect All Checkboxes
        $("#selectAll").on("click", function() {
            $(".rowCheckbox").prop("checked", this.checked);
        });
        
        $("#selectAll").on("click", function() {
            $(".rowCheckbox").prop("checked", this.checked);
        });

        function boldNumbersInInfo() {
            let info = $('.dataTables_info').html();
            info = info.replace(/(\d+)/g, '<strong>$1</strong>'); // Wrap numbers in <strong>
            $('.dataTables_info').html(info);
        }

        $('#clientTable').on('draw.dt', function() {
            boldNumbersInInfo();
        });

    });
</script>
<style>
    .custom-tooltip {
        position: relative;
        display: inline-block;
        cursor: pointer;
    }

    .custom-tooltip .tooltip-text {
        visibility: hidden;
        background-color: #222;
        /* Tooltip background */
        color: #fff;
        /* Tooltip text color */
        font-family: 'Arial', sans-serif;
        font-size: 12px;
        text-align: center;
        border-radius: 4px;
        padding: 4px 8px;
        position: absolute;
        z-index: 100;
        bottom: 120%;
        left: 50%;
        transform: translateX(-50%);
        opacity: 0;
        transition: opacity 0.2s ease-in-out;
        white-space: nowrap;
    }

    .custom-tooltip:hover .tooltip-text {
        visibility: visible;
        opacity: 1;
    }
</style>