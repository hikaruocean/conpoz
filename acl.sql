--
-- 資料表結構 `acl_actions`
--

CREATE TABLE `acl_actions` (
  `action_id` int(10) UNSIGNED NOT NULL,
  `controller_id` int(10) UNSIGNED NOT NULL,
  `name` varchar(64) NOT NULL,
  `descript` varchar(64) NOT NULL,
  `created` datetime NOT NULL,
  `updated` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


--
-- 資料表結構 `acl_controllers`
--

CREATE TABLE `acl_controllers` (
  `controller_id` int(10) UNSIGNED NOT NULL,
  `name` varchar(64) NOT NULL,
  `descript` varchar(64) NOT NULL,
  `created` datetime NOT NULL,
  `updated` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


--
-- 資料表結構 `acl_grants`
--

CREATE TABLE `acl_grants` (
  `grant_id` char(15) NOT NULL,
  `role_id` char(15) NOT NULL,
  `controller_id` int(10) UNSIGNED NOT NULL,
  `action_id` int(10) UNSIGNED NOT NULL,
  `created` datetime NOT NULL,
  `updated` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


--
-- 資料表結構 `acl_roles`
--

CREATE TABLE `acl_roles` (
  `role_id` char(15) NOT NULL,
  `name` varchar(64) NOT NULL,
  `admin` char(1) NOT NULL,
  `created` datetime NOT NULL,
  `updated` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


--
-- 資料表索引 `acl_actions`
--
ALTER TABLE `acl_actions`
  ADD PRIMARY KEY (`action_id`),
  ADD KEY `idx-controller_id` (`controller_id`) USING BTREE;

--
-- 資料表索引 `acl_controllers`
--
ALTER TABLE `acl_controllers`
  ADD PRIMARY KEY (`controller_id`);

--
-- 資料表索引 `acl_grants`
--
ALTER TABLE `acl_grants`
  ADD PRIMARY KEY (`grant_id`),
  ADD KEY `idx-role_id` (`role_id`);

--
-- 資料表索引 `acl_roles`
--
ALTER TABLE `acl_roles`
  ADD PRIMARY KEY (`role_id`);

