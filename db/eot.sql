-- phpMyAdmin SQL Dump
-- version 4.7.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: May 08, 2018 at 12:07 PM
-- Server version: 10.1.22-MariaDB
-- PHP Version: 7.0.18

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `eot`
--

-- --------------------------------------------------------

--
-- Table structure for table `eot_client`
--

CREATE TABLE `eot_client` (
  `clt_id` int(11) NOT NULL,
  `clt_compid` int(11) NOT NULL,
  `clt_fname` varchar(60) NOT NULL,
  `clt_lname` varchar(60) NOT NULL,
  `clt_payment_type` int(11) NOT NULL,
  `clt_address` varchar(200) NOT NULL,
  `clt_city` varchar(100) NOT NULL,
  `clt_state` varchar(100) NOT NULL,
  `clt_country` varchar(100) NOT NULL,
  `clt_zipcode` varchar(6) NOT NULL,
  `clt_lat` varchar(20) NOT NULL,
  `clt_long` varchar(20) NOT NULL,
  `clt_isactive` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1 - Active and 0 - deactive'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `eot_clt_acct_info`
--

CREATE TABLE `eot_clt_acct_info` (
  `acct_id` int(11) NOT NULL,
  `acct_cltid` int(11) NOT NULL,
  `acct_type` varchar(60) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `eot_company`
--

CREATE TABLE `eot_company` (
  `comp_id` int(11) NOT NULL,
  `comp_name` varchar(200) NOT NULL,
  `comp_email` varchar(60) NOT NULL,
  `comp_contact` varchar(20) NOT NULL,
  `comp_logo` varchar(60) NOT NULL,
  `comp_verification_code` varchar(6) NOT NULL,
  `comp_status` tinyint(1) NOT NULL DEFAULT '0',
  `comp_user_limit` int(3) NOT NULL COMMENT '1 - Verify and 0 - Unverify',
  `comp_job_limit` int(4) NOT NULL,
  `comp_isactive` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1 - Active and 0 - deactive',
  `comp_createdate` varchar(10) NOT NULL,
  `comp_updatedate` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `eot_comp_setting`
--

CREATE TABLE `eot_comp_setting` (
  `set_id` int(11) NOT NULL,
  `set_compid` int(11) NOT NULL,
  `set_city` int(100) NOT NULL,
  `set_state` int(100) NOT NULL,
  `set_country` int(100) NOT NULL,
  `set_email` varchar(60) NOT NULL,
  `set_duration` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `eot_contact`
--

CREATE TABLE `eot_contact` (
  `con_id` int(11) NOT NULL,
  `con_compid` int(11) NOT NULL,
  `con_fname` varchar(60) NOT NULL,
  `con_lname` varchar(60) NOT NULL,
  `con_email` varchar(60) NOT NULL,
  `con_mobile1` varchar(20) NOT NULL,
  `con_mobile2` varchar(20) NOT NULL,
  `con_fax` varchar(60) NOT NULL,
  `con_twitter` varchar(60) NOT NULL,
  `con_skype` varchar(60) NOT NULL,
  `con_isactive` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1 - Active and 0 - deactive'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `eot_job`
--

CREATE TABLE `eot_job` (
  `job_id` int(11) NOT NULL,
  `job_label` varchar(20) NOT NULL,
  `job_jtid` int(11) NOT NULL,
  `job_description` text NOT NULL,
  `job_type` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1 - Single Member and 2 - Multiple member',
  `job_priority` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1 - Low , 2 - Medium, 3 - High and 4 - Urgent',
  `job_status` tinyint(2) NOT NULL DEFAULT '1' COMMENT '1 - Not Started, 2 - Accepted, 3 - Reject , 4 - Cancel, 5 - Travelling, 6 - Break, 7 - In Progress , 8 - Pending, 9 - Completed, 10- Closed and 11 - Multi',
  `job_author` int(11) NOT NULL,
  `job_keeper` int(11) NOT NULL,
  `job_cltid` int(11) NOT NULL,
  `job_siteid` int(11) NOT NULL,
  `job_conid` int(11) NOT NULL,
  `job_shedule_start` varchar(10) NOT NULL,
  `job_shedule_finish` varchar(10) NOT NULL,
  `job_instruction` text NOT NULL,
  `job_email` int(11) NOT NULL,
  `job_mobile1` varchar(20) NOT NULL,
  `job_mobile2` varchar(20) NOT NULL,
  `job_address` varchar(200) NOT NULL,
  `job_city` varchar(100) NOT NULL,
  `job_state` varchar(100) NOT NULL,
  `job_country` varchar(100) NOT NULL,
  `job_zipcode` varchar(6) NOT NULL,
  `job_lat` varchar(20) NOT NULL,
  `job_long` varchar(20) NOT NULL,
  `job_isactive` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1 - Active and 0 - deactive',
  `job_createdate` varchar(10) NOT NULL,
  `job_updatedate` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `eot_job_member`
--

CREATE TABLE `eot_job_member` (
  `jm_id` int(11) NOT NULL,
  `jm_usrid` int(11) NOT NULL,
  `jm_jobid` int(11) NOT NULL,
  `jm_status` tinyint(2) NOT NULL DEFAULT '1' COMMENT '1 - Not Started, 2 - Accepted, 3 - Reject , 4 - Cancel, 5 - Travelling, 6 - Break, 7 - In Progress , 8 - Pending, 9 - Completed and 10- Closed',
  `jm_mem_status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1 - Active and 0 - deactive'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `eot_job_title`
--

CREATE TABLE `eot_job_title` (
  `jt_id` int(11) NOT NULL,
  `jt_compid` int(11) NOT NULL,
  `jt_title` varchar(100) NOT NULL,
  `jt_description` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `eot_site`
--

CREATE TABLE `eot_site` (
  `site_id` int(11) NOT NULL,
  `site_compid` int(11) NOT NULL,
  `site_name` varchar(200) NOT NULL,
  `site_address` varchar(200) NOT NULL,
  `site_city` varchar(100) NOT NULL,
  `site_state` varchar(100) NOT NULL,
  `site_country` varchar(100) NOT NULL,
  `site_zipcode` varchar(6) NOT NULL,
  `site_lat` varchar(20) NOT NULL,
  `site_long` varchar(20) NOT NULL,
  `site_isactive` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1 - Active and 0 - deactive'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `eot_status_log`
--

CREATE TABLE `eot_status_log` (
  `slog_id` int(11) NOT NULL,
  `slog_usrid` int(11) NOT NULL,
  `slog_jobid` int(11) NOT NULL,
  `slog_tskid` int(11) NOT NULL,
  `stlog_status` tinyint(2) NOT NULL COMMENT '1 - Not Started, 2 - Accepted, 3 - Reject , 4 - Cancel, 5 - Travelling, 6 - Break, 7 - In Progress , 8 - Pending, 9 - Completed and 10- Closed',
  `slog_time` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `eot_tag`
--

CREATE TABLE `eot_tag` (
  `tag_id` int(11) NOT NULL,
  `tag_name` varchar(60) NOT NULL,
  `tag_compid` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `eot_tag_mm`
--

CREATE TABLE `eot_tag_mm` (
  `tmm_id` int(11) NOT NULL,
  `tmm_tagid` int(11) NOT NULL,
  `tmm_tag_for` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1- job and 2 - task',
  `tmm_tag_forid` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `eot_task`
--

CREATE TABLE `eot_task` (
  `tsk_id` int(11) NOT NULL,
  `tsk_label` varchar(20) NOT NULL,
  `tsk_jobid` int(11) NOT NULL,
  `tsk_title` varchar(200) NOT NULL,
  `tsk_description` text NOT NULL,
  `tsk_author` int(11) NOT NULL,
  `tsk_keeper` int(11) NOT NULL,
  `tsk_priority` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1 - Low , 2 - Medium, 3 - High and 4 - Urgent',
  `tsk_type` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1 - Task, 2 - Bug, 3 - Change and 4 - Enhancement',
  `tsk_status` tinyint(2) NOT NULL DEFAULT '1' COMMENT '1 - Not Started, 2 - In Progress , 3 - Pending, 4 - Completed and 5- Closed',
  `tsk_isactive` int(1) NOT NULL DEFAULT '1' COMMENT '1 - Active and 0 - deactive',
  `tsk_createdate` varchar(10) NOT NULL,
  `tsk_updatedate` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `eot_task_member`
--

CREATE TABLE `eot_task_member` (
  `tm_id` int(11) NOT NULL,
  `tm_usrid` int(11) NOT NULL,
  `tm_tskid` int(11) NOT NULL,
  `tm_mem_status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1 - Active and 0 - deactive'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `eot_time_log`
--

CREATE TABLE `eot_time_log` (
  `log_id` int(11) NOT NULL,
  `log_usrid` int(11) NOT NULL,
  `log_jobid` int(11) NOT NULL,
  `log_tskid` int(11) NOT NULL,
  `log_login_time` varchar(10) NOT NULL,
  `log_logout_time` varchar(10) NOT NULL,
  `log_progress_status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '1 - Progress and 0 - End'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `eot_travel_log`
--

CREATE TABLE `eot_travel_log` (
  `tlog_id` int(11) NOT NULL,
  `tlog_usrid` int(11) NOT NULL,
  `tlog_jobid` int(11) NOT NULL,
  `log_tskid` int(11) NOT NULL,
  `tlog_login_time` varchar(10) NOT NULL,
  `tlog_logout_time` varchar(10) NOT NULL,
  `tlog_progress_status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '[1 - Progress and 0 - End'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `eot_user`
--

CREATE TABLE `eot_user` (
  `usr_id` int(11) NOT NULL,
  `usr_compid` int(11) NOT NULL,
  `usr_fname` varchar(60) NOT NULL,
  `usr_lname` varchar(60) NOT NULL,
  `usr_email` varchar(60) NOT NULL,
  `usr_password` varchar(200) NOT NULL,
  `usr_token` varchar(200) NOT NULL,
  `usr_image` varchar(60) NOT NULL,
  `usr_device_type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0 - Default(PC) , 1 - Android and 2 - IOS',
  `usr_device_id` varchar(300) NOT NULL DEFAULT '0',
  `usr_type` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1 - User , 2 - Admin and 3 - Super Admin',
  `usr_isactive` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1 - Active and 0 - deactive',
  `usr_createdate` varchar(10) NOT NULL,
  `usr_updatedate` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `eot_user`
--

INSERT INTO `eot_user` (`usr_id`, `usr_compid`, `usr_fname`, `usr_lname`, `usr_email`, `usr_password`, `usr_token`, `usr_image`, `usr_device_type`, `usr_device_id`, `usr_type`, `usr_isactive`, `usr_createdate`, `usr_updatedate`) VALUES
(2, 1, 'Subodh Kumar', 'Raypuriya', 'sk@gmail.com', 'e10adc3949ba59abbe56e057f20f883e', 'O:RU9UfDJ8M2FkYTFkZjZhNDIxNDg4MDZmODJlM2QxYzVjZTcxOWM=', '', 0, '', 1, 1, '', '1525704919');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `eot_client`
--
ALTER TABLE `eot_client`
  ADD PRIMARY KEY (`clt_id`);

--
-- Indexes for table `eot_clt_acct_info`
--
ALTER TABLE `eot_clt_acct_info`
  ADD PRIMARY KEY (`acct_id`);

--
-- Indexes for table `eot_company`
--
ALTER TABLE `eot_company`
  ADD PRIMARY KEY (`comp_id`);

--
-- Indexes for table `eot_comp_setting`
--
ALTER TABLE `eot_comp_setting`
  ADD PRIMARY KEY (`set_id`);

--
-- Indexes for table `eot_contact`
--
ALTER TABLE `eot_contact`
  ADD PRIMARY KEY (`con_id`);

--
-- Indexes for table `eot_job`
--
ALTER TABLE `eot_job`
  ADD PRIMARY KEY (`job_id`);

--
-- Indexes for table `eot_job_member`
--
ALTER TABLE `eot_job_member`
  ADD PRIMARY KEY (`jm_id`);

--
-- Indexes for table `eot_job_title`
--
ALTER TABLE `eot_job_title`
  ADD PRIMARY KEY (`jt_id`);

--
-- Indexes for table `eot_site`
--
ALTER TABLE `eot_site`
  ADD PRIMARY KEY (`site_id`);

--
-- Indexes for table `eot_tag`
--
ALTER TABLE `eot_tag`
  ADD PRIMARY KEY (`tag_id`);

--
-- Indexes for table `eot_tag_mm`
--
ALTER TABLE `eot_tag_mm`
  ADD PRIMARY KEY (`tmm_id`);

--
-- Indexes for table `eot_task`
--
ALTER TABLE `eot_task`
  ADD PRIMARY KEY (`tsk_id`);

--
-- Indexes for table `eot_task_member`
--
ALTER TABLE `eot_task_member`
  ADD PRIMARY KEY (`tm_id`);

--
-- Indexes for table `eot_time_log`
--
ALTER TABLE `eot_time_log`
  ADD PRIMARY KEY (`log_id`);

--
-- Indexes for table `eot_travel_log`
--
ALTER TABLE `eot_travel_log`
  ADD PRIMARY KEY (`tlog_id`);

--
-- Indexes for table `eot_user`
--
ALTER TABLE `eot_user`
  ADD PRIMARY KEY (`usr_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `eot_client`
--
ALTER TABLE `eot_client`
  MODIFY `clt_id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `eot_clt_acct_info`
--
ALTER TABLE `eot_clt_acct_info`
  MODIFY `acct_id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `eot_company`
--
ALTER TABLE `eot_company`
  MODIFY `comp_id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `eot_comp_setting`
--
ALTER TABLE `eot_comp_setting`
  MODIFY `set_id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `eot_contact`
--
ALTER TABLE `eot_contact`
  MODIFY `con_id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `eot_job`
--
ALTER TABLE `eot_job`
  MODIFY `job_id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `eot_job_member`
--
ALTER TABLE `eot_job_member`
  MODIFY `jm_id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `eot_job_title`
--
ALTER TABLE `eot_job_title`
  MODIFY `jt_id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `eot_site`
--
ALTER TABLE `eot_site`
  MODIFY `site_id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `eot_tag`
--
ALTER TABLE `eot_tag`
  MODIFY `tag_id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `eot_tag_mm`
--
ALTER TABLE `eot_tag_mm`
  MODIFY `tmm_id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `eot_task`
--
ALTER TABLE `eot_task`
  MODIFY `tsk_id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `eot_task_member`
--
ALTER TABLE `eot_task_member`
  MODIFY `tm_id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `eot_time_log`
--
ALTER TABLE `eot_time_log`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `eot_travel_log`
--
ALTER TABLE `eot_travel_log`
  MODIFY `tlog_id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `eot_user`
--
ALTER TABLE `eot_user`
  MODIFY `usr_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
