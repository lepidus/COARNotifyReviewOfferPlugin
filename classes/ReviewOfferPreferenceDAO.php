<?php

namespace APP\plugins\generic\coarNotifyReviewOffer\classes;

use PKP\db\DAO;
use Illuminate\Support\Facades\DB;
use APP\plugins\generic\coarNotifyReviewOffer\classes\ReviewOfferPreference;

class ReviewOfferPreferenceDAO extends DAO {
    private const TABLE_NAME = 'review_offer_preferences';

    /**
     * Get ReviewOfferPreference by submission ID.
     * @param $submissionId int Submission ID
     * @return ReviewOfferPreference
     */
    function getBySubmissionId($submissionId) {
        $result = DB::table(self::TABLE_NAME)
            ->where('submission_id', $submissionId)
            ->get();

        $reviewOffers = [];
        foreach ($result as $row) {
            $reviewOffers[] = $this->fromRow(get_object_vars($row));
        }

        return $reviewOffers;
    }

    /**
     * Insert a ReviewOfferPreference.
     * @param $preference ReviewOfferPreference
     * @return Void
     */
    function insertObject($preference) {
        DB::table(self::TABLE_NAME)->insert([
            'submission_id' => $preference->getSubmissionId(),
            'service_url' => $preference->getServiceUrl(),
            'is_sent' => (bool) $preference->getIsSent()
        ]);
    }

    function deleteByServiceUrlAndSubmissionId($serviceUrl, $submissionId) {
        DB::table(self::TABLE_NAME)
            ->where('service_url', (string) $serviceUrl)
            ->where('submission_id', (int) $submissionId)
            ->delete();
    }

    /**
     * Generate a new ReviewOfferPreference object.
     * @return ReviewOfferPreference
     */
    function newDataObject() {
        return new ReviewOfferPreference();
    }

    /**
     * Return a new ReviewOfferPreference object from a given row.
     * @return ReviewOfferPreference
     */
    function fromRow($row) {
        $preference = $this->newDataObject();
        $preference->setSubmissionId($row['submission_id']);
        $preference->setServiceUrl($row['service_url']);
        $preference->setIsSent($row['is_sent']);

        return $preference;
    }

}