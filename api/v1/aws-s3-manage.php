<?php
require __DIR__ .'/../../vendor/autoload.php';

use Aws\S3\S3Client;
use Aws\CloudFront\CloudFrontClient;
use Aws\Exception\AwsException;

class AwsS3
{
    public $bucketName = 'ezts-elearning';
    public $region = 'ap-south-1';
    public $distributionDomain = 'dw1larvlv4nev.cloudfront.net';

    function GetS3Client()
    {
        return new S3Client([
            'region'  => $this->region, // Replace with your S3 region
            'version' => 'latest',
            'credentials' => [
                'key'    => 'AKIA3M7AC6UDK4PQPIHH',
                'secret' => 'Bb5SkCp8872s/61FQj4aEyu51lbmCiAWvxoNSXqX',
            ],
        ]);
    }

    function Upload($video)
    {
        $s3 = $this->GetS3Client();

        $cloudFront = new CloudFrontClient([
            'region'  => $this->region, // Replace with your CloudFront region
            'version' => 'latest',
            'credentials' => [
                'key'    => 'AKIA3M7AC6UDK4PQPIHH',
                'secret' => 'Bb5SkCp8872s/61FQj4aEyu51lbmCiAWvxoNSXqX',
            ],
        ]);

        $fileName = $video['name'];
        $fileTempPath = $video['tmp_name'];

        $s3Url = null;
        $cloudFrontUrl = null;

        try {
            // Upload file to S3
            $result = $s3->putObject([
                'Bucket' => $this->bucketName,
                'Key'    => "uploads/{$fileName}", // You can specify a folder path if needed
                'SourceFile' => $fileTempPath,
                'ACL'    => 'public-read', // Make it public to be accessible via CloudFront
            ]);

            $s3Url = $result['ObjectURL'];

            // Create CloudFront Distribution (Only if needed, or you can skip this if you already have a distribution)
            $distributionConfig = [
                'CallerReference' => uniqid(),
                'Comment' => 'Distribution for e-learning platform',
                'Aliases' => [
                    'Quantity' => 0,
                ],
                'Origins' => [
                    'Quantity' => 1,
                    'Items' => [
                        [
                            'Id' => $this->bucketName,
                            'DomainName' => "{$this->bucketName}.s3.amazonaws.com",
                            'OriginPath' => '',
                            'CustomHeaders' => [
                                'Quantity' => 0,
                            ],
                            'S3OriginConfig' => [
                                'OriginAccessIdentity' => '',
                            ],
                        ],
                    ],
                ],
                'DefaultCacheBehavior' => [
                    'TargetOriginId' => $this->bucketName,
                    'ViewerProtocolPolicy' => 'redirect-to-https',
                    'AllowedMethods' => [
                        'Quantity' => 2,
                        'Items' => ['GET', 'HEAD'],
                        'CachedMethods' => [
                            'Quantity' => 2,
                            'Items' => ['GET', 'HEAD'],
                        ],
                    ],
                    'Compress' => true,
                    'DefaultTTL' => 86400,
                    'MinTTL' => 3600,
                    'ForwardedValues' => [
                        'QueryString' => false, // Set to true if you want to forward query strings
                        'Cookies' => [
                            'Forward' => 'none', // Options: 'none', 'whitelist', 'all'
                        ],
                        'Headers' => [
                            'Quantity' => 0, // Add headers to forward if needed
                        ],
                        'QueryStringCacheKeys' => [
                            'Quantity' => 0, // Add specific query string keys if needed
                        ],
                    ],
                ],
                'Enabled' => true,
            ];

            $distributionResult = $cloudFront->createDistribution([
                'DistributionConfig' => $distributionConfig,
            ]);

            $cloudFrontUrl = 'https://' . $distributionResult['Distribution']['DomainName'];

            // return Response::json(200, [
            //     'status' => 'success',
            //     'S3' => $s3Url,
            //     'CF' => $cloudFrontUrl
            // ]);

            return [
                'success',
                $s3Url
            ];
        } catch (AwsException $e) {
            // echo "Error: {$e->getMessage()}\n";
            // return Response::json(501, [
            //     'status' => 'error',
            //     'message' => $e->getMessage()
            // ]);

            return [
                'error',
                $e->getMessage()
            ];
        }
    }

    // Get signed url for client to authorize uploading
    function GetS3SignedUrl($keyName, $expiration = '+10 minutes')
    {
        $s3 = $this->GetS3Client();
        try {
            $cmd = $s3->getCommand('PutObject', [
                'Bucket' => $this->bucketName,
                'Key' => $keyName,
            ]);

            $request = $s3->createPresignedRequest($cmd, $expiration);

            // Return the signed URL
            return (string)$request->getUri();
        } catch (AwsException $e) {
            // Handle error
            error_log($e->getMessage());
            return null;
        }
    }


    public $privateKeyPath = './app/admin/secret/cloudfront key.pem';
    public $keyPairId = 'APKA3M7AC6UDOOOEWRUI';

    function GetCFSignedUrl($cloudfrontUrl)
    {
        $expireTime = time() + 60;
        $cloudfront = new CloudFrontClient([
            'version' => 'latest',
            'region'  => $this->region
        ]);

        $signedUrl = $cloudfront->getSignedUrl([
            'url'         => $cloudfrontUrl,
            'expires'     => $expireTime,
            'private_key' => $this->privateKeyPath,
            'key_pair_id' => $this->keyPairId
        ]);

        return $signedUrl;
    }
}
