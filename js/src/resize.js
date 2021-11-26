$(document).ready(function () {
    var firstheight = $('tr.prop_row_header').height();
    $("#proposals_table tr.prop_row_header:nth-child(2)").css("top", firstheight)

    var secondheight = $('#proposals_table tr.prop_row_header:nth-child(2)').height();
    $("#proposals_table tr.prop_row_header:nth-child(3)").css("top", firstheight + secondheight)


    let columnWidth = getColumnWidth();

    if (poll.env.user_auth) {
        $('#left-panel').width(columnWidth)
        $('#content').css("left", columnWidth)
    }

    interact('#content')
        .resizable({
            edges: { top: false, left: true, bottom: false, right: false },
            listeners: {
                move: function (event) {
                    let { x, y } = event.target.dataset

                    x = (parseFloat(x) || 0) + event.deltaRect.left
                    y = (parseFloat(y) || 0) + event.deltaRect.top


                    $('#left-panel').width(x + columnWidth);
                    Object.assign(event.target.style, {
                        width: `${event.rect.width}px`,
                        height: `${event.rect.height}px`,
                        transform: `translate(${x}px, ${y}px)`
                    })
                    window.localStorage.setItem('leftColumnWidth', window.innerWidth - event.rect.width)

                    Object.assign(event.target.dataset, { x, y })
                }
            }
        }).on('resizestart', function () {
            startDrag()
        });
});

function getColumnWidth() {
    let localWidth = window.localStorage.getItem('leftColumnWidth');
    if (localWidth) {
        columnWidth = localWidth >= 0 ? parseInt(localWidth) : 250;
        columnWidth = columnWidth >= window.innerWidth ? 250 : columnWidth;
    }
    else {
        columnWidth = 250;
    }
    return columnWidth;
}

function disableSelect(event) {
    event.preventDefault();
}

function startDrag(event) {
    window.addEventListener('mouseup', onDragEnd);
    window.addEventListener('selectstart', disableSelect);
}

function onDragEnd() {
    window.removeEventListener('mouseup', onDragEnd);
    window.removeEventListener('selectstart', disableSelect);
}