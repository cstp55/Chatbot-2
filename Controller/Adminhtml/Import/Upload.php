<?php
namespace Aprilo\Chatbot\Controller\Adminhtml\Import;

use Magento\Backend\App\Action;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\File\Csv;
use Magento\Framework\File\UploaderFactory;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\Filesystem\DirectoryList as FilesystemDirectoryList;

class Upload extends Action
{
    protected $csvProcessor;
    protected $directoryList;
    protected $fileUploaderFactory;
    protected $fileFactory;
    protected $filesystem;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        Csv $csvProcessor,
        FilesystemDirectoryList $directoryList,
        UploaderFactory $fileUploaderFactory,
        FileFactory $fileFactory,
        \Magento\Framework\Filesystem $filesystem
    ) {
        parent::__construct($context);
        $this->csvProcessor = $csvProcessor;
        $this->directoryList = $directoryList;
        $this->fileUploaderFactory = $fileUploaderFactory;
        $this->fileFactory = $fileFactory;
        $this->filesystem = $filesystem;
    }

    /**
     * Upload CSV action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        if ($this->_request->isPost() && isset($_FILES['csv_file_product']['name']) && $_FILES['csv_file_product']['name']) {
            try {
                // Get the upload path
                $path = $this->directoryList->getPath(DirectoryList::VAR_DIR) . '/chatbot/';

                // Check if the directory exists, if not, create it
                $this->createDirectoryIfNotExists($path);

                // Get the uploaded file and save it
                $uploader = $this->fileUploaderFactory->create(['fileId' => 'csv_file_product']);
                $uploader->setAllowedExtensions(['csv']);
                $uploader->setAllowCreateFolders(true);
                $uploader->setFilesDispersion(false);

                // Save the file in the specified directory
                $result = $uploader->save($path);

                // Get the full file path
                $filePath = $path . $result['file'];

                // Now process the CSV file
                $data = $this->csvProcessor->getData($filePath);

                // Process the data
                $this->processCsvData($data);

                // Success message
                $this->messageManager->addSuccessMessage(__('Product CSV file uploaded and processed successfully.'));
                return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath('chatbot/import/index');
            } catch (\Exception $e) {
                // Catch exceptions and show error message
                $this->messageManager->addErrorMessage(__('Error uploading file: ' . $e->getMessage()));
            }
        }

        // Redirect if something went wrong
        return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath('chatbot/import/index');
    }

    /**
     * Create directory if it does not exist
     *
     * @param string $path
     */
    private function createDirectoryIfNotExists($path)
    {
        // Get the filesystem write interface
        $directory = $this->filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);

        // Check if directory exists, if not, create it
        if (!$directory->isDirectory($path)) {
            $directory->create($path); // Create the directory if it doesn't exist
        }
    }

    /**
     * Process the CSV Data
     *
     * @param array $data
     */
    private function processCsvData($data)
    {

        // die('process data ');
        // Example: Loop through CSV data and process it (e.g., save to database, etc.)
        foreach ($data as $row) {
            // Assuming CSV contains columns like ID, Message, Response
            $id = $row[0];
            $message = $row[1];
            $response = $row[2];

            // Process or save the data as needed
            // e.g., $this->saveChatbotData($id, $message, $response);
        }
    }

    /**
     * Check if the admin user is allowed to perform this action
     *
     * @return bool
     */
    // protected function _isAllowed()
    // {
    //     return $this->_authorization->isAllowed('Aprilo_Chatbot::chatbot');
    // }
}
