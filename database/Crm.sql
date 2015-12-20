-- phpMyAdmin SQL Dump
-- version 4.2.2
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: 2015-12-20 23:00:23
-- 服务器版本： 5.5.37-log
-- PHP Version: 5.3.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `Crm`
--

-- --------------------------------------------------------

--
-- 表的结构 `account`
--

CREATE TABLE IF NOT EXISTS `account` (
`id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL COMMENT '用户id',
  `phone_num` varchar(1000) NOT NULL COMMENT '手机号',
  `password` varchar(100) NOT NULL COMMENT '密码',
  `status` tinyint(1) unsigned NOT NULL COMMENT '账户状态：0-无效 1-有效',
  `type` tinyint(1) unsigned NOT NULL COMMENT '账户状态：1-内部人员账户    2-业主联系人账户',
  `create_id` int(10) unsigned NOT NULL COMMENT '创建用户id，若为0则表明是系统创建或者是之前创建的无法追踪的信息',
  `regist_time` datetime NOT NULL COMMENT '账户注册时间',
  `level` tinyint(1) NOT NULL DEFAULT '1' COMMENT '权重',
  `email` varchar(100) DEFAULT NULL COMMENT '用户邮箱',
  `job_name` varchar(100) NOT NULL COMMENT '职位'
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='账户信息' AUTO_INCREMENT=3299 ;

--
-- 转存表中的数据 `account`
--

INSERT INTO `account` (`id`, `user_id`, `phone_num`, `password`, `status`, `type`, `create_id`, `regist_time`, `level`, `email`, `job_name`) VALUES
(1, 1, '18801168634', '14e1b600b1fd579f47433b88e8d85291', 1, 3, 0, '0000-00-00 00:00:00', 0, 'zhaoguang@nash.work', '');

-- --------------------------------------------------------

--
-- 表的结构 `comment`
--

CREATE TABLE IF NOT EXISTS `comment` (
  `id` int(10) unsigned NOT NULL,
  `event_id` int(10) unsigned NOT NULL COMMENT '事件id',
  `user_id` int(10) unsigned NOT NULL COMMENT '评论用户id',
  `content` tinyblob NOT NULL COMMENT '评论内容',
  `time` datetime NOT NULL COMMENT '评论时间',
  `status` tinyint(1) unsigned NOT NULL COMMENT '状态：0-无效 1-有效',
  `replay_id` int(10) unsigned NOT NULL COMMENT '回复评论id，不是回复性评论则写0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='评论表';

-- --------------------------------------------------------

--
-- 表的结构 `count`
--

CREATE TABLE IF NOT EXISTS `count` (
`id` int(10) unsigned NOT NULL,
  `name_id` int(10) NOT NULL COMMENT '发起人',
  `tag_name` varchar(255) NOT NULL COMMENT '标签id',
  `result` varchar(1000) NOT NULL COMMENT '统计结果',
  `begin_time` datetime NOT NULL COMMENT '统计开始时间',
  `end_time` datetime NOT NULL COMMENT '统计结束时间',
  `email` varchar(255) NOT NULL COMMENT '发送对象的email',
  `is_executed` tinyint(1) NOT NULL COMMENT '是否执行'
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='统计表' AUTO_INCREMENT=5 ;

-- --------------------------------------------------------

--
-- 表的结构 `event`
--

CREATE TABLE IF NOT EXISTS `event` (
`id` bigint(20) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL COMMENT '用户id',
  `content` mediumblob NOT NULL COMMENT '事件内容',
  `status` tinyint(1) unsigned NOT NULL COMMENT '事件状态：0-无效 1-有效',
  `create_time` datetime NOT NULL COMMENT '创建时间',
  `create_user_id` int(10) unsigned NOT NULL COMMENT '事件创建者id',
  `unread_num` tinyint(3) unsigned NOT NULL COMMENT '未读信息数',
  `photo` varchar(200) NOT NULL COMMENT '附带图片信息',
  `type` tinyint(1) unsigned NOT NULL COMMENT '事件类型: 1-系统事件 2-用户创建事件 3-群组事件',
  `see_range` int(10) unsigned NOT NULL COMMENT '事件查看范围，若为0则表示查看范围为公开，否则自定义用户群组id'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='事件信息' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `e_building`
--

CREATE TABLE IF NOT EXISTS `e_building` (
`id` int(10) unsigned NOT NULL COMMENT '主键',
  `name` varchar(4000) NOT NULL COMMENT '名称',
  `community` int(10) unsigned NOT NULL COMMENT '小区',
  `total_floor` int(10) unsigned DEFAULT NULL COMMENT '总层数',
  `total_lift` int(10) unsigned DEFAULT NULL COMMENT '总电梯数',
  `height` double unsigned DEFAULT NULL COMMENT '层高',
  `memo` varchar(4000) DEFAULT NULL COMMENT '备注',
  `state` smallint(5) unsigned NOT NULL DEFAULT '1' COMMENT '记录状态[0:无效]',
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '记录创建时间',
  `updated` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='楼座' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `e_room`
--

CREATE TABLE IF NOT EXISTS `e_room` (
`id` int(10) unsigned NOT NULL COMMENT '主键',
  `name` varchar(4000) NOT NULL COMMENT '名称',
  `building` int(10) unsigned NOT NULL COMMENT '楼座',
  `area` double unsigned DEFAULT NULL COMMENT '面积',
  `floor` int(10) unsigned DEFAULT NULL COMMENT '所在楼层',
  `orientation` smallint(5) unsigned DEFAULT NULL COMMENT '朝向[1:东 | 2:南 | 3:西 | 4:北]',
  `owner` int(10) unsigned DEFAULT NULL COMMENT '业主',
  `owner_name` varchar(4000) DEFAULT NULL COMMENT '业主姓名',
  `owner_gender` smallint(5) unsigned DEFAULT NULL COMMENT '业主性别[1:先生 | 2:女士]',
  `owner_phone` varchar(4000) DEFAULT NULL COMMENT '业主电话',
  `expected_rent` double DEFAULT NULL COMMENT '意向租金',
  `certification` smallint(5) unsigned DEFAULT NULL COMMENT '是否有证照',
  `certification_number` varchar(100) DEFAULT NULL COMMENT '证照号',
  `certification_body` varchar(100) DEFAULT NULL COMMENT '证照公司',
  `state` smallint(5) unsigned NOT NULL DEFAULT '1' COMMENT '记录状态[0:无效]',
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '记录创建时间',
  `updated` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '记录更新时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='房间' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `group`
--

CREATE TABLE IF NOT EXISTS `group` (
`id` int(10) unsigned NOT NULL COMMENT '主键',
  `name` varchar(20) NOT NULL COMMENT '群组名称',
  `create_user_id` int(10) unsigned NOT NULL COMMENT '创建用户id',
  `create_time` datetime NOT NULL COMMENT '创建时间',
  `status` tinyint(1) NOT NULL COMMENT '状态 0-无效 1-有效',
  `list` varchar(1000) NOT NULL COMMENT '成员列表，格式json，字典内容为联系人id＋联系人类型',
  `profile` varchar(1000) NOT NULL COMMENT '群组信息，格式使用json',
  `group_type` tinyint(3) unsigned NOT NULL COMMENT '群组类型  0-无类型    1-房间',
  `group_project` int(10) unsigned NOT NULL COMMENT '群组所属项目对应tag_id'
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='群组表' AUTO_INCREMENT=12376 ;

--
-- 转存表中的数据 `group`
--

INSERT INTO `group` (`id`, `name`, `create_user_id`, `create_time`, `status`, `list`, `profile`, `group_type`, `group_project`) VALUES
(7, '测试的群组222', 1, '0000-00-00 00:00:00', 1, '', '[]', 0, 0),
(8, '首城 777', 1, '2015-06-25 10:29:22', 1, '', '[]', 0, 0),
(9, '金贸 A0309', 757, '2015-06-25 15:14:24', 1, '', '', 1, 473),
(10, '官网项目', 3, '2015-06-25 17:28:53', 1, '', '', 0, 0),
(11, '首城 828', 1711, '2015-06-25 22:34:55', 1, '', '', 1, 695),
(12, '金贸大厦A0936', 757, '2015-06-25 22:39:10', 1, '', '', 1, 473),
(13, '首城B501', 1711, '2015-06-25 22:49:05', 1, '', '[]', 1, 695),
(14, '金贸A2116', 1082, '2015-06-26 11:27:53', 1, '', '', 1, 473),
(15, '首城国际服务中心', 1715, '2015-06-26 11:51:38', 1, '', '[]', 0, 695),
(16, '金贸A2208', 757, '2015-06-26 17:53:09', 1, '', '{"company":"\\u5f00\\u6e90\\u8bc1\\u5238"}', 1, 473),
(17, '551', 1711, '2015-06-26 19:10:08', 1, '', '', 1, 695),
(18, '30320', 1712, '2015-06-29 09:58:45', 1, '', '', 1, 15),
(19, '', 259, '2015-06-29 13:21:11', 1, '', '', 1, 16),
(20, '买卖汇石', 259, '2015-06-29 13:22:08', 1, '', '{"introduction":"\\u738b\\u5973\\u58eb13901032770"}', 1, 15);

-- --------------------------------------------------------

--
-- 表的结构 `group_list`
--

CREATE TABLE IF NOT EXISTS `group_list` (
`id` int(10) unsigned NOT NULL COMMENT '主键',
  `group_id` int(10) unsigned NOT NULL COMMENT '群组id',
  `user_id` int(10) unsigned NOT NULL COMMENT '用户id',
  `add_user_id` int(10) unsigned NOT NULL COMMENT '添加操作用户id',
  `remark` varchar(10) NOT NULL,
  `add_time` datetime NOT NULL COMMENT '添加时间',
  `event_id` int(10) unsigned NOT NULL COMMENT '关联事件id'
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='群组成员列表' AUTO_INCREMENT=1386 ;

--
-- 转存表中的数据 `group_list`
--

INSERT INTO `group_list` (`id`, `group_id`, `user_id`, `add_user_id`, `remark`, `add_time`, `event_id`) VALUES
(6, 7, 511, 1, '看房者', '2015-06-25 00:00:00', 3220),
(8, 7, 1922, 1, '业主', '2015-06-25 10:07:58', 3224),
(10, 8, 366, 1, '业主', '2015-06-25 10:35:14', 3232);

-- --------------------------------------------------------

--
-- 表的结构 `group_tag`
--

CREATE TABLE IF NOT EXISTS `group_tag` (
`id` int(10) unsigned NOT NULL COMMENT '主键',
  `group_id` int(10) unsigned NOT NULL COMMENT '群组id',
  `tag_id` int(10) unsigned NOT NULL COMMENT '标签id',
  `bind_time` datetime NOT NULL COMMENT '绑定时间',
  `event_id` int(10) unsigned NOT NULL COMMENT '获取标签对应事件id'
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='群组绑定标签信息' AUTO_INCREMENT=30313 ;

--
-- 转存表中的数据 `group_tag`
--

INSERT INTO `group_tag` (`id`, `group_id`, `tag_id`, `bind_time`, `event_id`) VALUES
(70, 12, 14, '2015-06-25 22:40:06', 3277),
(71, 12, 473, '2015-06-25 22:40:06', 3277),
(72, 13, 14, '2015-06-25 22:50:55', 3280),
(73, 13, 695, '2015-06-25 22:50:55', 3280),
(74, 13, 1665, '2015-06-25 22:51:15', 3281),
(75, 12, 13, '2015-06-25 23:28:36', 3282),
(76, 12, 473, '2015-06-25 23:28:36', 3282),
(77, 12, 1601, '2015-06-25 23:28:36', 3282),
(78, 12, 1349, '2015-06-26 11:26:59', 3291),
(79, 14, 13, '2015-06-26 11:33:01', 3292),
(80, 14, 517, '2015-06-26 11:33:01', 3292),
(81, 15, 695, '2015-06-26 11:56:12', 3293),
(82, 15, 1585, '2015-06-26 11:56:12', 3293),
(83, 13, 14, '2015-06-26 12:50:36', 3298),
(84, 13, 695, '2015-06-26 12:50:36', 3298),
(85, 15, 695, '2015-06-26 16:32:16', 3322),
(86, 15, 1618, '2015-06-26 16:32:16', 3322),
(87, 15, 1585, '2015-06-26 16:32:16', 3322),
(88, 15, 695, '2015-06-26 16:33:33', 3323),
(89, 15, 1618, '2015-06-26 16:33:33', 3323),
(90, 15, 13, '2015-06-26 17:35:17', 3327),
(91, 15, 695, '2015-06-26 17:35:17', 3327),
(92, 15, 1585, '2015-06-26 17:35:17', 3327),
(93, 15, 695, '2015-06-26 17:47:12', 3328),
(94, 15, 1614, '2015-06-26 17:47:12', 3328),
(95, 15, 1618, '2015-06-26 17:47:12', 3328),
(97, 12, 1585, '2015-06-26 18:45:01', 3336),
(98, 12, 13, '2015-06-27 15:20:55', 3344),
(99, 12, 473, '2015-06-27 15:20:55', 3344),
(100, 12, 13, '2015-06-27 15:23:45', 3345),
(101, 12, 473, '2015-06-27 15:23:45', 3345),
(102, 18, 12, '2015-06-29 10:07:49', 3349),
(103, 18, 1526, '2015-06-29 10:07:49', 3349),
(104, 20, 1672, '2015-06-29 13:30:33', 3356),
(105, 15, 695, '2015-06-29 13:55:26', 3357),
(108, 1000, 16, '2015-06-29 14:20:53', 3359),
(10638, 13, 14, '2015-06-29 15:16:10', 8627),
(10639, 13, 695, '2015-06-29 15:16:10', 8627),
(10657, 15, 695, '2015-06-29 15:32:49', 8640),
(10658, 15, 1618, '2015-06-29 15:32:49', 8640),
(10660, 16, 12, '2015-06-29 18:09:53', 8657),
(10661, 16, 13, '2015-06-29 18:09:53', 8657),
(10662, 16, 473, '2015-06-29 18:09:53', 8657),
(10663, 16, 519, '2015-06-29 18:09:53', 8657),
(10664, 12, 1412, '2015-06-29 18:11:36', 8658),
(10665, 12, 1585, '2015-06-29 18:11:36', 8658),
(10669, 16, 12, '2015-06-29 18:15:25', 8660),
(10670, 16, 13, '2015-06-29 18:15:25', 8660),
(10671, 16, 473, '2015-06-29 18:15:25', 8660),
(10672, 16, 519, '2015-06-29 18:15:25', 8660),
(10694, 18, 12, '2015-06-30 15:19:53', 8694),
(10695, 18, 15, '2015-06-30 15:19:53', 8694),
(10696, 18, 1526, '2015-06-30 15:19:53', 8694),
(10699, 14, 1595, '2015-06-30 17:51:47', 8706),
(10700, 14, 1412, '2015-06-30 17:51:47', 8706),
(10701, 12, 518, '2015-06-30 17:54:35', 8707),
(10708, 15, 695, '2015-06-30 20:15:33', 8727),
(10709, 15, 1618, '2015-06-30 20:15:33', 8727),
(10727, 15, 695, '2015-07-01 13:45:35', 8758),
(10728, 15, 1618, '2015-07-01 13:45:35', 8758),
(10737, 15, 14, '2015-07-01 16:40:08', 8774),
(10738, 9, 14, '2015-07-01 17:07:17', 8780),
(10739, 9, 473, '2015-07-01 17:07:17', 8780),
(10740, 9, 1598, '2015-07-01 17:07:17', 8780),
(10784, 15, 1618, '2015-07-02 17:39:15', 8868),
(10785, 14, 1596, '2015-07-02 17:41:10', 8869),
(10786, 14, 1412, '2015-07-02 17:41:10', 8869),
(10789, 15, 1618, '2015-07-02 23:30:30', 8878),
(10790, 15, 6252, '2015-07-02 23:30:30', 8878),
(10797, 13, 13, '2015-07-02 23:51:08', 8881),
(10798, 13, 14, '2015-07-02 23:51:08', 8881),
(10799, 13, 695, '2015-07-02 23:51:08', 8881),
(10800, 13, 1599, '2015-07-02 23:51:08', 8881),
(10801, 13, 1601, '2015-07-02 23:51:08', 8881),
(10802, 13, 519, '2015-07-02 23:51:08', 8881),
(10803, 13, 1585, '2015-07-02 23:51:08', 8881),
(10804, 13, 13, '2015-07-03 10:41:53', 8882),
(10805, 13, 1610, '2015-07-03 10:41:53', 8882),
(10820, 18, 12, '2015-07-03 12:53:39', 8894),
(10821, 18, 15, '2015-07-03 12:53:39', 8894),
(10822, 18, 1526, '2015-07-03 12:53:39', 8894),
(10825, 18, 12, '2015-07-03 15:06:15', 8901),
(10826, 18, 15, '2015-07-03 15:06:15', 8901),
(10827, 18, 1526, '2015-07-03 15:06:15', 8901),
(10834, 15, 695, '2015-07-03 15:36:32', 8913),
(10835, 15, 1618, '2015-07-03 15:36:32', 8913),
(10861, 18, 12, '2015-07-04 17:39:23', 8944),
(10862, 18, 15, '2015-07-04 17:39:23', 8944),
(10863, 18, 1592, '2015-07-04 17:39:23', 8944),
(10868, 10, 6277, '2015-07-05 17:25:03', 8948),
(10899, 13, 695, '2015-07-06 16:10:46', 8986),
(10900, 13, 1618, '2015-07-06 16:10:46', 8986),
(10933, 15, 14, '2015-07-07 13:53:13', 9025),
(10964, 15, 695, '2015-07-07 17:59:03', 9045),
(10965, 15, 1585, '2015-07-07 17:59:03', 9045),
(10995, 9, 14, '2015-07-08 15:47:26', 9104),
(10996, 9, 473, '2015-07-08 15:47:26', 9104),
(10997, 9, 1598, '2015-07-08 15:47:26', 9104),
(11000, 15, 695, '2015-07-08 17:11:58', 9115),
(11001, 15, 1585, '2015-07-08 17:11:58', 9115),
(11002, 15, 1618, '2015-07-08 22:08:28', 9135),
(11003, 15, 1618, '2015-07-08 22:09:35', 9136),
(11005, 15, 14, '2015-07-09 11:33:08', 9143),
(11297, 1000, 6306, '2015-07-10 10:46:08', 0),
(16611, 9, 13, '2015-07-10 19:48:36', 9401),
(16612, 9, 6280, '2015-07-10 19:48:36', 9401),
(16613, 9, 473, '2015-07-10 19:48:36', 9401),
(16632, 9, 1597, '2015-07-13 15:38:46', 9433),
(16664, 9, 6280, '2015-07-14 16:37:28', 9506),
(16784, 9, 14, '2015-07-20 17:04:03', 9693),
(16785, 9, 1599, '2015-07-20 17:04:03', 9693),
(16997, 9, 12, '2015-07-24 16:42:31', 10012),
(16998, 9, 1412, '2015-07-24 16:42:31', 10012),
(17018, 9, 13, '2015-07-27 11:55:30', 10032),
(17019, 9, 1585, '2015-07-27 11:55:30', 10032),
(17139, 15, 695, '2015-07-28 18:11:23', 10139),
(17140, 15, 1585, '2015-07-28 18:11:23', 10139),
(17234, 9, 13, '2015-07-31 20:15:12', 10322),
(17235, 9, 473, '2015-07-31 20:15:12', 10322),
(17499, 14, 13, '2015-08-12 17:31:59', 10812),
(17507, 1000, 12, '2015-08-12 17:50:51', 10816);

-- --------------------------------------------------------

--
-- 表的结构 `job`
--

CREATE TABLE IF NOT EXISTS `job` (
`id` int(10) unsigned NOT NULL COMMENT '职位表',
  `job_name` varchar(100) NOT NULL COMMENT '职位名称',
  `created_time` datetime NOT NULL COMMENT '创建时间',
  `updated_time` datetime NOT NULL COMMENT '修改时间'
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=6 ;

--
-- 转存表中的数据 `job`
--

INSERT INTO `job` (`id`, `job_name`, `created_time`, `updated_time`) VALUES
(1, '其它', '2015-08-14 00:00:00', '2015-08-14 00:00:00');

-- --------------------------------------------------------

--
-- 表的结构 `login`
--

CREATE TABLE IF NOT EXISTS `login` (
  `id` int(11) unsigned NOT NULL COMMENT '后台登陆表',
  `name` varchar(50) NOT NULL COMMENT '用户名',
  `password` char(32) NOT NULL COMMENT '密码',
  `created_time` datetime NOT NULL COMMENT '创建时间'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- 转存表中的数据 `login`
--

INSERT INTO `login` (`id`, `name`, `password`, `created_time`) VALUES
(1, 'admin', 'e10adc3949ba59abbe56e057f20f883e', '2015-08-14 00:00:00');

-- --------------------------------------------------------

--
-- 表的结构 `notice`
--

CREATE TABLE IF NOT EXISTS `notice` (
`id` bigint(20) unsigned NOT NULL,
  `from_user_id` int(10) unsigned NOT NULL COMMENT '创建者id',
  `to_user_id` int(10) unsigned NOT NULL COMMENT '接受者id',
  `content` mediumblob NOT NULL COMMENT '通知内容',
  `status` tinyint(1) unsigned NOT NULL COMMENT '状态：0-无效  1-有效',
  `param` bigint(20) unsigned NOT NULL COMMENT '事件id',
  `is_read` tinyint(1) unsigned NOT NULL COMMENT '是否已读',
  `read_time` datetime DEFAULT NULL COMMENT '读取时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='通知队列信息' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `queue`
--

CREATE TABLE IF NOT EXISTS `queue` (
`id` int(10) unsigned NOT NULL COMMENT '信息队列表',
  `taskphp` varchar(150) NOT NULL COMMENT '发送的地址',
  `param` varchar(4000) NOT NULL COMMENT '发送的参数',
  `create_time` datetime NOT NULL COMMENT '创建时间',
  `status` int(11) NOT NULL COMMENT '状态 0 未发送 200 成功 1001参数错误 2 未成功插入数据库 ',
  `status_time` datetime NOT NULL COMMENT '处理时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `tag`
--

CREATE TABLE IF NOT EXISTS `tag` (
`id` int(10) unsigned NOT NULL,
  `name` varchar(50) NOT NULL,
  `create_time` datetime NOT NULL,
  `color` varchar(50) NOT NULL COMMENT '显示颜色信息',
  `type` smallint(5) unsigned NOT NULL COMMENT '类型： 0到999是系统创建标签 1000之后是用户创建标签',
  `form_key` varchar(50) DEFAULT NULL
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='标签信息' AUTO_INCREMENT=13412 ;

--
-- 转存表中的数据 `tag`
--

INSERT INTO `tag` (`id`, `name`, `create_time`, `color`, `type`, `form_key`) VALUES
(12, '业主', '2015-04-15 00:00:00', 'white-text amber lighten-2', 1, NULL),
(13, '租户', '2015-04-15 00:00:00', 'white-text light-blue lighten-2', 1, NULL),
(14, '中介', '2015-04-15 00:00:00', 'white-text purple lighten-2', 1, NULL);

-- --------------------------------------------------------

--
-- 表的结构 `token`
--

CREATE TABLE IF NOT EXISTS `token` (
  `user_id` int(10) unsigned NOT NULL COMMENT '用户id',
  `token` varchar(100) NOT NULL COMMENT '授权令牌信息',
  `refresh_token` varchar(100) NOT NULL COMMENT '刷新授权令牌信息',
  `create_time` datetime NOT NULL COMMENT '创建时间',
  `expire_time` datetime NOT NULL COMMENT '过期时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='授权信息';

-- --------------------------------------------------------

--
-- 表的结构 `user_event`
--

CREATE TABLE IF NOT EXISTS `user_event` (
  `user_id` int(10) unsigned NOT NULL COMMENT '用户id',
  `event_id` int(10) unsigned NOT NULL COMMENT '事件id',
  `time` datetime NOT NULL COMMENT '事件',
  `type` tinyint(1) unsigned NOT NULL COMMENT '类型：1-赞',
  `is_read` tinyint(1) unsigned NOT NULL COMMENT '是否已读',
  `read_time` datetime DEFAULT NULL COMMENT '读取时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='用户针对事件处理信息';

-- --------------------------------------------------------

--
-- 表的结构 `user_id_tmp`
--

CREATE TABLE IF NOT EXISTS `user_id_tmp` (
`id` int(10) unsigned NOT NULL,
  `time` datetime NOT NULL COMMENT '换取时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='用于生成用户id' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `user_log`
--

CREATE TABLE IF NOT EXISTS `user_log` (
  `user_id` int(10) NOT NULL COMMENT '用户id',
  `API_name` varchar(100) NOT NULL COMMENT '调用api接口名称',
  `param` varchar(1000) NOT NULL COMMENT '调用参数字符串化',
  `time` datetime NOT NULL COMMENT '调用时间',
`id` int(10) unsigned NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='用户调用接口日志' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `user_profile`
--

CREATE TABLE IF NOT EXISTS `user_profile` (
  `user_id` int(10) unsigned NOT NULL COMMENT '用户id',
  `nickname` tinyblob NOT NULL COMMENT '昵称',
  `sex` tinyint(1) unsigned NOT NULL COMMENT '性别：0-保密  1-女 2-男',
  `user_profile` mediumtext NOT NULL COMMENT '用户信息，使用字符串格式化形式记录',
  `update_time` datetime DEFAULT NULL COMMENT '用户信息更新时间',
  `photo` varchar(200) NOT NULL COMMENT '头像地址'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='用户基本信息';

--
-- 转存表中的数据 `user_profile`
--

INSERT INTO `user_profile` (`user_id`, `nickname`, `sex`, `user_profile`, `update_time`, `photo`) VALUES
(1, 0xe5bca0e6af85e6988e, 0, 'a:2:{s:3:"sex";a:2:{s:3:"sex";s:6:"性别";s:5:"value";s:3:"男";}s:10:"occupation";a:2:{s:10:"occupation";s:6:"职业";s:5:"value";s:12:"投资经理";}}', '2015-09-24 12:13:34', 'avatar/2015/06/1_7ccb8ac39e23204725157d3d87d5a726_s.jpg');

-- --------------------------------------------------------

--
-- 表的结构 `user_tag`
--

CREATE TABLE IF NOT EXISTS `user_tag` (
  `user_id` int(10) unsigned NOT NULL COMMENT '用户id',
  `tag_id` int(10) unsigned NOT NULL COMMENT '标签id',
  `bind_time` datetime NOT NULL COMMENT '绑定时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='用户拥有标签信息';

--
-- 转存表中的数据 `user_tag`
--

INSERT INTO `user_tag` (`user_id`, `tag_id`, `bind_time`) VALUES
(1, 12, '2015-05-12 11:33:06');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `account`
--
ALTER TABLE `account`
 ADD PRIMARY KEY (`id`), ADD KEY `phone_num` (`phone_num`(255));

--
-- Indexes for table `comment`
--
ALTER TABLE `comment`
 ADD PRIMARY KEY (`id`);

--
-- Indexes for table `count`
--
ALTER TABLE `count`
 ADD PRIMARY KEY (`id`);

--
-- Indexes for table `event`
--
ALTER TABLE `event`
 ADD PRIMARY KEY (`id`), ADD KEY `user_id` (`user_id`), ADD KEY `create_user_id` (`create_user_id`);

--
-- Indexes for table `e_building`
--
ALTER TABLE `e_building`
 ADD PRIMARY KEY (`id`), ADD KEY `building_fk_community` (`community`);

--
-- Indexes for table `e_room`
--
ALTER TABLE `e_room`
 ADD PRIMARY KEY (`id`), ADD KEY `room_fk_building` (`building`);

--
-- Indexes for table `group`
--
ALTER TABLE `group`
 ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `group_list`
--
ALTER TABLE `group_list`
 ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `group_id` (`group_id`,`user_id`), ADD KEY `event_id` (`event_id`);

--
-- Indexes for table `group_tag`
--
ALTER TABLE `group_tag`
 ADD PRIMARY KEY (`id`), ADD KEY `event_id` (`event_id`), ADD KEY `group_id` (`group_id`,`tag_id`);

--
-- Indexes for table `job`
--
ALTER TABLE `job`
 ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notice`
--
ALTER TABLE `notice`
 ADD PRIMARY KEY (`id`), ADD KEY `param` (`param`), ADD KEY `to_user_id` (`to_user_id`);

--
-- Indexes for table `queue`
--
ALTER TABLE `queue`
 ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tag`
--
ALTER TABLE `tag`
 ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `token`
--
ALTER TABLE `token`
 ADD PRIMARY KEY (`user_id`), ADD UNIQUE KEY `token` (`token`);

--
-- Indexes for table `user_event`
--
ALTER TABLE `user_event`
 ADD PRIMARY KEY (`user_id`,`event_id`);

--
-- Indexes for table `user_id_tmp`
--
ALTER TABLE `user_id_tmp`
 ADD PRIMARY KEY (`id`);

--
-- Indexes for table `user_log`
--
ALTER TABLE `user_log`
 ADD PRIMARY KEY (`id`);

--
-- Indexes for table `user_profile`
--
ALTER TABLE `user_profile`
 ADD PRIMARY KEY (`user_id`), ADD KEY `update_time` (`update_time`);

--
-- Indexes for table `user_tag`
--
ALTER TABLE `user_tag`
 ADD PRIMARY KEY (`user_id`,`tag_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `account`
--
ALTER TABLE `account`
MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=3299;
--
-- AUTO_INCREMENT for table `count`
--
ALTER TABLE `count`
MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=5;
--
-- AUTO_INCREMENT for table `event`
--
ALTER TABLE `event`
MODIFY `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `e_building`
--
ALTER TABLE `e_building`
MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键';
--
-- AUTO_INCREMENT for table `e_room`
--
ALTER TABLE `e_room`
MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键';
--
-- AUTO_INCREMENT for table `group`
--
ALTER TABLE `group`
MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',AUTO_INCREMENT=12376;
--
-- AUTO_INCREMENT for table `group_list`
--
ALTER TABLE `group_list`
MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',AUTO_INCREMENT=1386;
--
-- AUTO_INCREMENT for table `group_tag`
--
ALTER TABLE `group_tag`
MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',AUTO_INCREMENT=30313;
--
-- AUTO_INCREMENT for table `job`
--
ALTER TABLE `job`
MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '职位表',AUTO_INCREMENT=6;
--
-- AUTO_INCREMENT for table `notice`
--
ALTER TABLE `notice`
MODIFY `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `queue`
--
ALTER TABLE `queue`
MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '信息队列表';
--
-- AUTO_INCREMENT for table `tag`
--
ALTER TABLE `tag`
MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=13412;
--
-- AUTO_INCREMENT for table `user_id_tmp`
--
ALTER TABLE `user_id_tmp`
MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `user_log`
--
ALTER TABLE `user_log`
MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
