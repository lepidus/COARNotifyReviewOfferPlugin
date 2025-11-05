<link rel="stylesheet" type="text/css" href="/plugins/generic/coarNotifyReviewOffer/styles/coarNotifyReviewOffer.css">
<script type="text/javascript" src="/plugins/generic/coarNotifyReviewOffer/js/coar-notify.js"></script>

<script type="text/javascript">
    $(function() {ldelim}
        $('#reviewOfferPreferences').pkpHandler('$.pkp.controllers.form.AjaxFormHandler');
        {rdelim});
</script>


{load_header context="backend"}

<div id="coarNotifyReviewOffer">
    <div id="historyHeader">
        <h2>{translate key="plugins.generic.coarNotifyReviewOffer.title"}</h2>
    </div>
    {if $isPublished}
        <p>{translate key="plugins.generic.coarNotifyReviewOffer.reviewOfferDescription"}</p>
        <p>{translate key="plugins.generic.coarNotifyReviewOffer.reviewOfferDescriptionPartTwo"}</p>

        <h3>{translate key="plugins.generic.coarNotifyReviewOffer.services"}</h3>
        <div>
            {capture assign=sendNotificationUrl}{url router=$smarty.const.ROUTE_COMPONENT component="plugins.generic.coarNotifyReviewOffer.controllers.CoarNotifyProxyHandler" op="sendNotification" escape=false}{/capture}
            {foreach from=$reviewServiceList key=targetHomeUrl item=targetInboxUrl name=reviewServiceList}
                <div class="reviewBlock">
                    <h4 class="reviewServiceHomeUrl">{$targetHomeUrl}</h4>
                    <button
                            id="{$smarty.foreach.reviewServiceList.index}-send-button"
                            class="askForReviewButton"
                            onclick="sendNotificationHandler(
                                    '{$sendNotificationUrl}',
                                    '{$originInboxUrl}',
                                    '{$originHomeUrl}',
                                    '{$targetInboxUrl}',
                                    '{$targetHomeUrl}',
                                    '{$authorId}',
                                    '{$actorName}',
                                    '{$doi}',
                                    '{$smarty.foreach.reviewServiceList.index}-send-button'
                                    )"
                    >
                        {translate key="plugins.generic.coarNotifyReviewOffer.askForReviews"}
                    </button>
                </div>
            {/foreach}
        </div>

    {else}
        <div>{translate key="plugins.generic.coarNotifyReviewOffer.prePubDescription"}</div>

        <div id="reviewOfferPreferences">
            {capture assign=reviewOfferPrefsGridUrl}{url router=$smarty.const.ROUTE_COMPONENT component="plugins.generic.coarNotifyReviewOffer.controllers.grid.CoarReviewOfferGridHandler" op="fetchGrid" submissionId=$submission->getId() escape=false}{/capture}
            {load_url_in_div id="reviewOfferPrefsGridContainer"|uniqid url=$reviewOfferPrefsGridUrl}
        </div>
    {/if}
</div>