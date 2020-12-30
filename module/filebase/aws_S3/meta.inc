<?php

	global $AWS_S3_USEDFLAG;
	global $AWS_S3_ACCESS_KEY;
	global $AWS_S3_SEACRET_KEY;
	global $AWS_S3_BUCKET_NAME;
	global $AWS_S3_PARTITION;
	global $AWS_S3_DEBUG ;
	global $AWS_S3_DEBUG_DIR ;

	include 'include/extends/amazon/aws.phar';
	include_once 'module/filebase/aws_S3/custom/conf/awsS3Conf.php';
	include_once 'module/filebase/aws_S3/custom/model/S3.php';

	global $S3;
	$S3 = new S3();

	include_once 'module/filebase/aws_S3/custom/model/awsS3FileBase.php';
