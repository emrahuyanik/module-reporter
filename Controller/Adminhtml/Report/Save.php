<?php
/**
 * Copyright © ismail cakir All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Kodhub\Reporter\Controller\Adminhtml\Report;

use Magento\Framework\Exception\LocalizedException;

class Save extends \Magento\Backend\App\Action
{

    protected $dataPersistor;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor
    ) {
        $this->dataPersistor = $dataPersistor;
        parent::__construct($context);
    }

    /**
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $data = $this->getRequest()->getPostValue();

        if ($data) {
            $id = $this->getRequest()->getParam('report_id');

            $model = $this->_objectManager->create(\Kodhub\Reporter\Model\Report::class)->load($id);
            if (!$model->getId() && $id) {
                $this->messageManager->addErrorMessage(__('This Report no longer exists.'));
                return $resultRedirect->setPath('*/*/');
            }

            if (
                isset($data['query_parameters_container']) &&
                is_array($data['query_parameters_container']) &&
                count($data['query_parameters_container']) > 0
            ) {
                $data['query_parameters'] = json_encode($data['query_parameters_container']);
            } else {
                $data['query_parameters'] = json_encode([]);
            }

            if (
                isset($data['cron_email_list_container']) &&
                is_array($data['cron_email_list_container']) &&
                count($data['cron_email_list_container']) > 0
            ) {
                $data['cron_email_list'] = json_encode($data['cron_email_list_container']);
            } else {
                $data['cron_email_list'] = json_encode([]);
            }

            $model->setData($data);

            try {
                $model->save();
                $this->messageManager->addSuccessMessage(__('You saved the Report.'));
                $this->dataPersistor->clear('kodhub_reporter_report');

                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['report_id' => $model->getId()]);
                }
                return $resultRedirect->setPath('*/*/');
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the Report.'));
            }

            $this->dataPersistor->set('kodhub_reporter_report', $data);
            return $resultRedirect->setPath('*/*/edit', ['report_id' => $this->getRequest()->getParam('report_id')]);
        }
        return $resultRedirect->setPath('*/*/');
    }
}

