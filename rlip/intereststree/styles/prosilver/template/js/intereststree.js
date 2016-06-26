var intTree = {};
intTree.currentNode = {
    label: "Drzewo zainteresowań",
    id: 0
};

intTree.onNodeClick = function (node) {
    intTree.currentNode = node;
    if (node.selectionAllowed) {
        $('#rate-header').show();
        $('#rateit5')
            .rateit('value', node.rate);
    } else {
        $('#rate-header').hide();
    }
    intTree.updateProposalContainer();
};

intTree.setRateAjax = function (iRate) {
    jQuery.ajax({
        url: '/app.php/intereststree/setRate',
        dataType: 'json',
        method: 'post',
        data: {
            id: intTree.currentNode.id,
            rate: iRate
        },
        success: function (data) {
            if (!data.success) {
                alert(data.message || 'Wystąpił nieznany błąd');
            } else {
                intTree.currentNode.rate = iRate;
                $('#refresh-counters-btn').show();
            }
        },
        error: function (data) {
            alert('Wystąpił nieznany błąd');
        }
    });
};

intTree.onRatedSet = function (event, value) {
    intTree.setRateAjax(value);

};
intTree.onRatedReset = function (event, value) {
    intTree.setRateAjax(0);
};

intTree.addProposal = function () {
    var proposal = prompt('Co chciałabyś/chciałbyś dodać/zmienić w węźle: "' + intTree.currentNode.label + '"?', '');
    if (!proposal) {
        return;
    }
    jQuery.ajax({
        url: '/app.php/intereststree/addProposal',
        dataType: 'json',
        method: 'post',
        data: {
            id: intTree.currentNode.id,
            proposal: proposal
        },
        success: function (data) {
            alert(data.message || 'Wystąpił nieznany błąd');
            intTree.updateProposalContainer();
        },
        error: function (data) {
            alert('Wystąpił nieznany błąd');
        }
    });
};

intTree.updateProposalContainer = function () {
    jQuery.ajax({
        url: '/app.php/intereststree/getProposalContainer',
        dataType: 'html',
        method: 'post',
        data: {
            id: intTree.currentNode.id
        },
        success: function (data) {
            $('#proposal-container').html(data);
        },
        error: function (data) {
            alert('Wystąpił nieznany błąd');
        }
    });
};

intTree.onProposalVote = function(element, isPlus) {
    var jElement = $(element),
        row = jElement.parents('.row'),
        vote = row.find('.vote');

    jQuery.ajax({
        url: '/app.php/intereststree/proposalVote',
        dataType: 'json',
        method: 'post',
        data: {
            proposal_id: row.data('proposalId'),
            is_plus: isPlus
        },
        success: function (data) {
            row.find('.vote-plus').text('+' + data.count_plus);
            row.find('.vote-minus').text('-' + data.count_minus);
            vote.removeClass('no-voted');
            if(isPlus){
                vote.removeClass('voted-minus').addClass('voted-plus');
            } else {
                vote.removeClass('voted-plus').addClass('voted-minus');
            }
        },
        error: function (data) {
            alert('Wystąpił nieznany błąd');
        }
    });
}
