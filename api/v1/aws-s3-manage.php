<?php
require './vendor/autoload.php';

use Aws\S3\S3Client;
use Aws\CloudFront\CloudFrontClient;
use Aws\Exception\AwsException;

class AwsS3
{
    function Upload($video)
    {
        $s3 = new S3Client([
            'region'  => 'ap-south-1', // Replace with your S3 region
            'version' => 'latest',
            'credentials' => [
                'key'    => 'AKIA3M7AC6UDK4PQPIHH',
                'secret' => 'Bb5SkCp8872s/61FQj4aEyu51lbmCiAWvxoNSXqX',
            ],
        ]);

        $cloudFront = new CloudFrontClient([
            'region'  => 'ap-south-1', // Replace with your CloudFront region
            'version' => 'latest',
            'credentials' => [
                'key'    => 'AKIA3M7AC6UDK4PQPIHH',
                'secret' => 'Bb5SkCp8872s/61FQj4aEyu51lbmCiAWvxoNSXqX',
            ],
        ]);

        $bucketName = 'ezts-elearning';
        $fileName = $video['name'];
        $fileTempPath = $video['tmp_name'];

        $s3Url = null;
        $cloudFrontUrl = null;

        try {
            // Upload file to S3
            $result = $s3->putObject([
                'Bucket' => $bucketName,
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
                            'Id' => $bucketName,
                            'DomainName' => "{$bucketName}.s3.amazonaws.com",
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
                    'TargetOriginId' => $bucketName,
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
}
