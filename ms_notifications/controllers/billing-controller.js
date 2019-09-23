const billingService = require('./../services/billing-service');

const automaticBillingEnabled = (request, response) => {
    billingService.enableAutomaticBilling(1, 11, 2018);
    response.status(200).json({
        status_code: 'success'
    });
};

const invoiceCreated = async (req, res) => {
    // TODO needs validation
    const { school_id, month, year } = req.body;
    await billingService.invoiceCreated(school_id, month, year);
    res.status(200).json({
        status_code: 'success'
    });
}

module.exports = { automaticBillingEnabled, invoiceCreated };