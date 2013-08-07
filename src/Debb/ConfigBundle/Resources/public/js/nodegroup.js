$(function () {
    $('#node-chooser').on('change', function () {
        var selected = $(this).find('option:selected');
        if (typeof(selected.attr('img')) != 'undefined') {
            $('#node-pic').css('background-image', 'url("' + selected.attr('img') + '")');
        }
        else {
            $('#node-pic').css('background-image', '');
        }
        if (typeof(selected.attr('text')) != 'undefined') {
            $('#node-pic').html(selected.attr('text'));
            $('#node-pic [rel="tooltip"]').tooltip();
        }
        else {
            $('#node-pic').html('');
        }
    });
    $(document).on('click', '.selectNode', function (e) {
        if ($('.adopt').attr('field') != null) {
            $('#debb_configbundle_nodegrouptype_nodes_' + $('.adopt').attr('field') + '_node').parents('.node').removeClass('nodeSelected');
        }
        var nodeId = $(this).parents('.node').find('[id$="_node"]').val(),
            field = getExactId($(this).parents('.node').find('div:first').attr('id'));
        if (nodeId == null) {
            nodeId = 0;
        }
        $('#node-chooser').val(nodeId);
        $('#node-chooser').change();
        $('.adopt').show();
        $('.adopt').attr('field', field);
        var typspec = $(this).parents('.node[specification]').attr('specification');
        $('#nodegroupType').html(' (' + Translator.get('Type') + ' ' + typspec + ')');
        $('#node-chooser option').each(function () {
            if (typeof($(this).attr('nodetyp')) != 'undefined') {
                if ($(this).attr('nodetyp') != typspec && typspec != '') {
                    $(this).prop('disabled', true).css('color', '#BBB');
                }
                else {
                    $(this).prop('disabled', false).css('color', '#555');
                }
            }
        });
        $(this).parents('.node').addClass('nodeSelected');
        e.preventDefault();
    });
    $('#debb_configbundle_nodegrouptype_draft').change(function () {
        var obj = $(this).find('option:selected:first[typspecs]');
        if (obj.length > 0) {
            $('#nodegroup').css('background-image', 'url("' + obj.attr('image') + '")');
            $('#nodegroup').width(parseInt(obj.attr('sizex')));
            $('#nodegroup').height(parseInt(obj.attr('sizey')));
            $('#content').width(parseInt($('#nodegroup').width()) + 559);

            var typspecs = $.parseJSON(obj.attr('typspecs')),
                nodeArr = $('#nodegroup').find('.node');

            if (nodeArr.length > typspecs.length) {
                for (var x = typspecs.length; x < nodeArr.length; x++) {
                    $(nodeArr[x]).remove();
                }
            }
            else {
                for (var x = nodeArr.length; x < typspecs.length; x++) {
                    var id = getFreeId('debb_configbundle_nodegrouptype_nodes_', x);
                    $('#nodegroup').append($('#nodegroup').attr('data-prototype').replace(/__name__/g, getExactId(id)));
                    $('#' + id + '_nodeGroup').val(nodeGroupId);
                }
            }

            var field = 0;
            nodeArr = $('#nodegroup').find('.node');

            nodeArr.each(function()
            {
                $(this).css('position', 'absolute').css('left', typspecs[field].posX).css('bottom', typspecs[field].posY).attr('specification', typspecs[field].typ);
                $(this).find('[id$="_field"]').val(++field);
            });

            updateNodes();
        }
    });
    $('.adopt').on('click', function (e) {
        var field = $(this).attr('field');
        $('#debb_configbundle_nodegrouptype_nodes_' + field + '_node').val($('#node-chooser').val() > 0 ? $('#node-chooser').val() : '');
        $('#debb_configbundle_nodegrouptype_nodes_' + field + '_node').parents('.node').removeClass('nodeSelected');
        $('.adopt').removeAttr('field');
        $('#node-chooser').val(0);
        $('#node-chooser').change();
        $('.adopt').hide();
        $('#node-chooser option').prop('disabled', false).css('color', '#555');
        $('#nodegroupType').html('');
        updateNodes();
        e.preventDefault();
    });
    $('#node-chooser').change();
    $('#debb_configbundle_nodegrouptype_draft').change();
    updateNodes();
});

function updateNodes() {
    $('.nodeTitle').each(function () {
        var node = $(this).parents('.node'),
            nodeId = $(node).find('[id$="_node"]').val();
        $(this).html($('#node-chooser [value="' + (nodeId == null || nodeId == '' ? '0' : nodeId) + '"]').html());
    });
    var nodes = $('.node'),
        y = 0;
    for (var x = nodes.length - 1; x >= 0; x--) {
        $(nodes[x]).find('input[id$="_field"]').val(y);
        y++;
    }
}

function getExactId(str) {
    var array = str.split('_');
    return array[array.length - 1];
}

function getFreeId(name, id) {
    if (typeof(id) == 'undefined') {
        id = 0;
    }
    if ($('#' + name + id).length > 0 || $('.' + name + id).length > 0) {
        return getFreeId(name, parseInt(id) + 1);
    }
    return name + id;
}
