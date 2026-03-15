jQuery(document).ready(function($) {
    'use strict';

    // Move modal to body
    const modal = $('#ufhec-modal');
    if (modal.length) {
        modal.appendTo('body');
    }

    const openBtn = $('#ufhec-open-btn');
    const closeBtn = $('.ufhec-close');
    const cancelBtn = $('.ufhec-btn-cancel');
    const form = $('#ufhec-form');
    const messageDiv = $('#ufhec-msg');

    // Open modal
    openBtn.on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        modal.addClass('show').fadeIn(300);
        $('body').css('overflow', 'hidden');
    });

    // Close modal
    function closeModal() {
        modal.removeClass('show').fadeOut(300);
        $('body').css('overflow', '');
        form[0].reset();
        messageDiv.hide().removeClass('success error');
    }

    closeBtn.on('click', closeModal);
    cancelBtn.on('click', closeModal);

    // Close when clicking outside
    modal.on('click', function(e) {
        if (e.target === modal[0]) {
            closeModal();
        }
    });

    // Prevent modal content clicks from closing
    $('.ufhec-modal-content').on('click', function(e) {
        e.stopPropagation();
    });

    // Form submission
    form.on('submit', function(e) {
        e.preventDefault();

        const submitBtn = form.find('button[type="submit"]');
        const formData = new FormData(this);
        
        formData.append('action', 'ufhec_submit');
        formData.append('nonce', ufhecPub.nonce);

        submitBtn.prop('disabled', true).text('Enviando...');
        messageDiv.hide();

        $.ajax({
            url: ufhecPub.ajaxurl,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    messageDiv
                        .removeClass('error')
                        .addClass('success')
                        .html(response.data.message)
                        .fadeIn();
                    
                    form[0].reset();
                    
                    setTimeout(function() {
                        closeModal();
                    }, 3000);
                } else {
                    messageDiv
                        .removeClass('success')
                        .addClass('error')
                        .html(response.data.message)
                        .fadeIn();
                }
            },
            error: function() {
                messageDiv
                    .removeClass('success')
                    .addClass('error')
                    .html('Error al enviar. Inténtalo de nuevo.')
                    .fadeIn();
            },
            complete: function() {
                submitBtn.prop('disabled', false).text('Enviar');
            }
        });
    });

    // Table functionality
    if ($('#ufhec-table').length) {
        const table = $('#ufhec-table');
        const tbody = table.find('tbody');
        const rows = tbody.find('tr');
        const searchInput = $('#ufhec-search');
        const filterAno = $('#ufhec-filter-ano');
        const filterCuartil = $('#ufhec-filter-cuartil');
        const resetBtn = $('#ufhec-reset');
        const visibleCount = $('#ufhec-visible');
        const totalCount = $('#ufhec-total');

        // Search functionality
        searchInput.on('keyup', function() {
            filterTable();
        });

        // Filter functionality
        filterAno.on('change', filterTable);
        filterCuartil.on('change', filterTable);

        // Reset filters
        resetBtn.on('click', function() {
            searchInput.val('');
            filterAno.val('');
            filterCuartil.val('');
            filterTable();
        });

        // Filter table function
        function filterTable() {
            const searchTerm = searchInput.val().toLowerCase();
            const anoFilter = filterAno.val();
            const cuartilFilter = filterCuartil.val();
            let visibleRows = 0;

            rows.each(function() {
                const row = $(this);
                const text = row.text().toLowerCase();
                const ano = row.data('ano');
                const cuartil = row.data('cuartil');

                let showRow = true;

                // Search filter
                if (searchTerm && !text.includes(searchTerm)) {
                    showRow = false;
                }

                // Year filter
                if (anoFilter && ano != anoFilter) {
                    showRow = false;
                }

                // Cuartil filter
                if (cuartilFilter && cuartil != cuartilFilter) {
                    showRow = false;
                }

                if (showRow) {
                    row.removeClass('hidden');
                    visibleRows++;
                } else {
                    row.addClass('hidden');
                }
            });

            visibleCount.text(visibleRows);
        }

        // Sorting functionality
        let sortDirection = {};

        table.find('thead th.sortable').on('click', function() {
            const th = $(this);
            const sortKey = th.data('sort');
            const currentDirection = sortDirection[sortKey] || 'none';
            
            // Remove sort classes from all headers
            table.find('thead th').removeClass('sort-asc sort-desc');
            
            // Determine new direction
            let newDirection;
            if (currentDirection === 'none' || currentDirection === 'desc') {
                newDirection = 'asc';
                th.addClass('sort-asc');
            } else {
                newDirection = 'desc';
                th.addClass('sort-desc');
            }
            
            sortDirection = {};
            sortDirection[sortKey] = newDirection;

            // Sort rows
            const sortedRows = rows.sort(function(a, b) {
                let aVal, bVal;

                if (sortKey === 'titulo') {
                    aVal = $(a).find('td:first').text();
                    bVal = $(b).find('td:first').text();
                } else if (sortKey === 'ano') {
                    aVal = parseInt($(a).data('ano')) || 0;
                    bVal = parseInt($(b).data('ano')) || 0;
                } else if (sortKey === 'revista') {
                    aVal = $(a).find('td:nth-child(6)').text();
                    bVal = $(b).find('td:nth-child(6)').text();
                } else if (sortKey === 'cuartil') {
                    const cuartilOrder = { 'Q1': 1, 'Q2': 2, 'Q3': 3, 'Q4': 4, '': 5 };
                    aVal = cuartilOrder[$(a).data('cuartil')] || 5;
                    bVal = cuartilOrder[$(b).data('cuartil')] || 5;
                }

                if (typeof aVal === 'string') {
                    aVal = aVal.toLowerCase();
                    bVal = bVal.toLowerCase();
                }

                if (newDirection === 'asc') {
                    return aVal > bVal ? 1 : -1;
                } else {
                    return aVal < bVal ? 1 : -1;
                }
            });

            tbody.html(sortedRows);
        });

        // Initialize count
        totalCount.text(rows.length);
        visibleCount.text(rows.length);
    }
});
