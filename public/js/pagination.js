window.renderPaginationTable = function ({
    pagerSelector,
    tableBodySelector,
    rows,
    emptyRowHtml,
    pageSize = 10
}) {
    const tableBody = document.querySelector(tableBodySelector);
    const pager = window.jQuery ? window.jQuery(pagerSelector) : null;

    if (!tableBody || !pager || typeof pager.pagination !== 'function') {
        return;
    }

    if (pager.data('pagination')) {
        pager.pagination('destroy');
    }

    if (!Array.isArray(rows) || rows.length === 0) {
        tableBody.innerHTML = emptyRowHtml;
        return;
    }

    pager.pagination({
        dataSource: rows,
        pageSize,
        showSizeChanger: false,
        callback: function (data) {
            tableBody.innerHTML = data.join('');
        }
    });
};
