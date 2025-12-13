-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost
-- Tiempo de generación: 13-12-2025 a las 05:15:41
-- Versión del servidor: 8.0.44
-- Versión de PHP: 8.2.29

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `inventory-procurement-spa`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `inventory`
--

/*

CREATE TABLE `inventory` (
  `ProductId` int NOT NULL,
  `InStock` int NOT NULL,
  `NextExpirationDate` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;*/

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `products`
--

CREATE TABLE `products` (
  `ProductId` int NOT NULL,
  `Sku` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `Name` varchar(225) COLLATE utf8mb4_general_ci NOT NULL,
  `Category` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `Brand` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `SupplierId` int NOT NULL,
  `Price` decimal(10,2) NOT NULL,
  `InStock` int NOT NULL,
  `NextExpirationDate` date NOT NULL,
  `CreateAT` date NOT NULL,
  `UpdateAt` varchar(20) COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `products`
--

INSERT INTO `products` 
(`ProductId`, `Sku`, `Name`, `Category`, `Brand`, `SupplierId`, `Price`, `InStock`, `NextExpirationDate`, `CreateAT`, `UpdateAt`) VALUES
(1, 'PROD-ONE-OVER', 'Product One Overwritten', 'Electronics', 'LogiPro', 1, 12.75, 120, '2026-01-15', '2025-12-12 20:40:01', '2025-12-12 20:40:01'),
(2, 'SUP1-MS-002', 'Wireless Mouse', 'Electronics', 'LogiPro', 1, 24.50, 85, '2026-02-10', '2025-12-12 12:49:15', '2025-12-12 12:49:15'),
(3, 'SUP1-HD-003', 'HDMI Cable 2m', 'Electronics', 'CableMax', 1, 8.75, 200, '2027-05-01', '2025-12-12 12:49:15', '2025-12-12 12:49:15'),
(4, 'SUP1-LS-004', 'Laptop Stand', 'Accessories', 'ErgoLift', 1, 29.99, 60, '2026-03-20', '2025-12-12 12:49:15', '2025-12-12 12:49:15'),
(5, 'SUP2-CH-001', 'Office Chair', 'Furniture', 'FurniCo', 2, 139.99, 40, '2030-12-31', '2025-12-12 12:49:15', '2025-12-12 12:49:15'),
(6, 'SUP2-DS-002', 'Office Desk', 'Furniture', 'FurniCo', 2, 249.00, 25, '2030-12-31', '2025-12-12 12:49:15', '2025-12-12 12:49:15'),
(7, 'SUP2-DR-003', 'Drawer Cabinet', 'Furniture', 'FurniCo', 2, 89.50, 30, '2030-12-31', '2025-12-12 12:49:15', '2025-12-12 12:49:15'),
(8, 'SUP2-LP-004', 'Desk Lamp', 'Furniture', 'BrightHome', 2, 34.99, 75, '2028-06-15', '2025-12-12 12:49:15', '2025-12-12 12:49:15'),
(9, 'SUP3-PP-001', 'A4 Paper Pack (500)', 'Stationery', 'PaperLine', 3, 6.99, 500, '2027-01-01', '2025-12-12 12:49:15', '2025-12-12 12:49:15'),
(10, 'SUP3-NB-002', 'Notebook A5', 'Stationery', 'PaperLine', 3, 3.25, 350, '2027-01-01', '2025-12-12 12:49:15', '2025-12-12 12:49:15'),
(11, 'SUP3-PN-003', 'Ballpoint Pens (10)', 'Stationery', 'InkJoy', 3, 4.80, 400, '2027-01-01', '2025-12-12 12:49:15', '2025-12-12 12:49:15'),
(12, 'SUP3-HL-004', 'Highlighter Set', 'Stationery', 'InkJoy', 3, 5.60, 150, '2027-01-01', '2025-12-12 12:49:15', '2025-12-12 12:49:15'),
(13, 'SUP4-BX-001', 'Cardboard Box Small', 'Packaging', 'EcoPack', 4, 1.20, 1000, '2028-12-31', '2025-12-12 12:49:15', '2025-12-12 12:49:15'),
(14, 'SUP4-BX-002', 'Cardboard Box Medium', 'Packaging', 'EcoPack', 4, 2.40, 800, '2028-12-31', '2025-12-12 12:49:15', '2025-12-12 12:49:15'),
(15, 'SUP4-TP-003', 'Packing Tape', 'Packaging', 'EcoPack', 4, 3.10, 600, '2028-12-31', '2025-12-12 12:49:15', '2025-12-12 12:49:15'),
(16, 'SUP4-BW-004', 'Bubble Wrap Roll', 'Packaging', 'EcoPack', 4, 14.99, 250, '2028-12-31', '2025-12-12 12:49:15', '2025-12-12 12:49:15'),
(17, 'SUP5-SG-001', 'Safety Gloves', 'Safety', 'SafeWorks', 5, 7.80, 300, '2026-09-01', '2025-12-12 12:49:15', '2025-12-12 12:49:15'),
(18, 'SUP5-SH-002', 'Safety Helmet', 'Safety', 'SafeWorks', 5, 22.50, 120, '2028-05-01', '2025-12-12 12:49:15', '2025-12-12 12:49:15'),
(19, 'SUP5-RV-003', 'Reflective Vest', 'Safety', 'SafeWorks', 5, 9.99, 200, '2028-05-01', '2025-12-12 12:49:15', '2025-12-12 12:49:15'),
(20, 'SUP5-PG-004', 'Protective Glasses', 'Safety', 'SafeWorks', 5, 6.45, 180, '2028-05-01', '2025-12-12 12:49:15', '2025-12-12 12:49:15'),
(21, 'SUP6-ET-001', 'Ethernet Cable 5m', 'Networking', 'NetGearPro', 6, 11.20, 220, '2029-01-01', '2025-12-12 12:49:15', '2025-12-12 12:49:15'),
(22, 'SUP6-WR-002', 'WiFi Router', 'Networking', 'NetGearPro', 6, 79.99, 90, '2029-01-01', '2025-12-12 12:49:15', '2025-12-12 12:49:15'),
(23, 'SUP6-SW-003', 'Network Switch 8-Port', 'Networking', 'NetGearPro', 6, 49.90, 70, '2029-01-01', '2025-12-12 12:49:15', '2025-12-12 12:49:15'),
(24, 'SUP6-PP-004', 'Patch Panel', 'Networking', 'NetGearPro', 6, 39.00, 50, '2029-01-01', '2025-12-12 12:49:15', '2025-12-12 12:49:15'),
(25, 'SUP7-CS-001', 'Cleaning Spray', 'Cleaning', 'CleanPro', 7, 5.40, 300, '2026-03-01', '2025-12-12 12:49:15', '2025-12-12 12:49:15'),
(26, 'SUP7-MC-002', 'Microfiber Cloth (5)', 'Cleaning', 'CleanPro', 7, 6.99, 400, '2026-03-01', '2025-12-12 12:49:15', '2025-12-12 12:49:15'),
(27, 'SUP7-FM-003', 'Floor Mop', 'Cleaning', 'CleanPro', 7, 14.50, 150, '2026-03-01', '2025-12-12 12:49:15', '2025-12-12 12:49:15'),
(28, 'SUP7-BK-004', 'Bucket 10L', 'Cleaning', 'CleanPro', 7, 8.30, 200, '2026-03-01', '2025-12-12 12:49:15', '2025-12-12 12:49:15'),
(29, 'SUP7-DG-005', 'Disinfectant Gel', 'Cleaning', 'CleanPro', 7, 4.75, 500, '2026-03-01', '2025-12-12 12:49:15', '2025-12-12 12:49:15'),
(30, 'SUP7-TB-006', 'Trash Bags (50)', 'Cleaning', 'CleanPro', 7, 6.20, 600, '2026-03-01', '2025-12-12 12:49:15', '2025-12-12 12:49:15'),
(31, 'SUP4-MN-011', 'Monitor 27 pulgadas', 'Electronics', 'ViewMax Pro', 1, 300.50, 40, '2029-12-31', '2025-12-12 18:01:35', '2025-12-12 18:01:35'),
(32, 'USBC-1M-001', 'USB-C Cable 1m', 'Electronics', 'Generic', 1, 9.99, 350, '2029-12-31', '2025-12-12 20:42:13', '2025-12-12 20:42:13');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `purchaseorder`
--

CREATE TABLE `purchaseorder` (
  `OrderId` int NOT NULL,
  `SupplierId` int NOT NULL,
  `TotalAmount` decimal(10,2) NOT NULL,
  `Status` varchar(20) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'PENDING',
  `OrderDate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `CreateAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `UpdateAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `purchaseorder`
--

INSERT INTO `purchaseorder` (`OrderId`, `SupplierId`, `TotalAmount`, `Status`, `OrderDate`, `CreateAt`, `UpdateAt`) VALUES
(1, 1, 48.73, 'APPROVED', '2025-12-12 19:43:38', '2025-12-12 19:43:38', '2025-12-12 20:40:48'),
(2, 1, 148.95, 'PENDING', '2025-12-12 19:52:41', '2025-12-12 19:52:41', '2025-12-12 19:52:41'),
(3, 2, 8.75, 'PENDING', '2025-12-12 19:52:48', '2025-12-12 19:52:48', '2025-12-12 19:52:48'),
(4, 1, 299.90, 'PENDING', '2025-12-12 19:52:54', '2025-12-12 19:52:54', '2025-12-12 19:52:54'),
(5, 3, 213.49, 'PENDING', '2025-12-12 19:52:59', '2025-12-12 19:52:59', '2025-12-12 19:52:59'),
(6, 2, 74.98, 'PENDING', '2025-12-12 19:53:04', '2025-12-12 19:53:04', '2025-12-12 19:53:04'),
(7, 1, 839.94, 'PENDING', '2025-12-12 19:53:09', '2025-12-12 19:53:09', '2025-12-12 19:53:09'),
(8, 3, 79.97, 'PENDING', '2025-12-12 19:53:16', '2025-12-12 19:53:16', '2025-12-12 19:53:16'),
(9, 2, 196.00, 'PENDING', '2025-12-12 19:53:21', '2025-12-12 19:53:21', '2025-12-12 19:53:21'),
(10, 1, 86.23, 'PENDING', '2025-12-12 19:53:26', '2025-12-12 19:53:26', '2025-12-12 19:53:26'),
(11, 3, 188.99, 'PENDING', '2025-12-12 19:53:30', '2025-12-12 19:53:30', '2025-12-12 19:53:30'),
(12, 1, 50.00, 'PENDING', '2025-12-12 20:40:28', '2025-12-12 20:40:28', '2025-12-12 20:40:28');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `purchaseorderitems`
--

CREATE TABLE `purchaseorderitems` (
  `OrderId` int NOT NULL,
  `ProductId` int NOT NULL,
  `Quantity` int NOT NULL,
  `PriceAtPurchase` decimal(10,2) NOT NULL
) ;

--
-- Volcado de datos para la tabla `purchaseorderitems`
--

INSERT INTO `purchaseorderitems` (`OrderId`, `ProductId`, `Quantity`, `PriceAtPurchase`) VALUES
(1, 1, 2, 19.99),
(1, 3, 1, 8.75),
(2, 1, 5, 19.99),
(2, 2, 2, 24.50),
(3, 3, 1, 8.75),
(4, 4, 10, 29.99),
(5, 2, 3, 24.50),
(5, 5, 1, 139.99),
(6, 1, 2, 19.99),
(6, 3, 4, 8.75),
(7, 5, 6, 139.99),
(8, 1, 1, 19.99),
(8, 4, 2, 29.99),
(9, 2, 8, 24.50),
(10, 3, 3, 8.75),
(10, 4, 2, 29.99),
(11, 2, 2, 24.50),
(11, 5, 1, 139.99),
(12, 1, 2, 12.75),
(12, 2, 1, 24.50);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `suppliers`
--

CREATE TABLE `suppliers` (
  `SupplierId` int NOT NULL,
  `Name` varchar(225) COLLATE utf8mb4_general_ci NOT NULL,
  `Email` varchar(225) COLLATE utf8mb4_general_ci NOT NULL,
  `Role` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `Status` enum('Active','Inactive') COLLATE utf8mb4_general_ci NOT NULL,
  `CreateAt` datetime NOT NULL,
  `UpdateAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `suppliers`
--

INSERT INTO `suppliers` (`SupplierId`, `Name`, `Email`, `Role`, `Status`, `CreateAt`, `UpdateAt`) VALUES
(1, 'Supplier One Updated', 'supplier1@updated.com', 'supplier', 'Active', '2025-12-12 12:49:15', '2025-12-12 20:38:23'),
(2, 'North Tech Distribution', 'sales@northtech.ca', 'vendor', 'Active', '2025-12-12 12:49:15', '2025-12-12 12:49:15'),
(3, 'Eco Packaging Ltd', 'info@ecopackaging.com', 'vendor', 'Active', '2025-12-12 12:49:15', '2025-12-12 12:49:15'),
(4, 'Fast Logistics Inc', 'support@fastlogistics.com', 'vendor', 'Active', '2025-12-12 12:49:15', '2025-12-12 12:49:15'),
(5, 'Industrial Safety Co', 'orders@industrialsafety.com', 'vendor', 'Active', '2025-12-12 12:49:15', '2025-12-12 12:49:15'),
(6, 'Network Solutions Group', 'sales@netsolutions.com', 'vendor', 'Active', '2025-12-12 12:49:15', '2025-12-12 12:49:15'),
(7, 'Cleaning Essentials Ltd', 'contact@cleanessentials.com', 'vendor', 'Active', '2025-12-12 12:49:15', '2025-12-12 12:49:15'),
(8, 'Proveedor Test', 'newEmail@supplier.com', 'supplier', 'Inactive', '2025-12-12 16:53:42', '2025-12-12 16:59:07'),
(9, 'NorthStar Supplies', 'northstar@supplies.com', 'supplier', 'Active', '2025-12-12 20:37:18', '2025-12-12 20:37:18'),
(10, 'Pacific Trade Co.', 'pacific@trade.com', 'supplier', 'Active', '2025-12-12 20:37:28', '2025-12-12 20:37:28');

--
-- Índices para tablas volcadas
--

--
/*-- Indices de la tabla `inventory`
--
ALTER TABLE `inventory`
  ADD PRIMARY KEY (`ProductId`);*/

--
-- Indices de la tabla `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`ProductId`),
  ADD UNIQUE KEY `Sku` (`Sku`),
  ADD KEY `SupplierId` (`SupplierId`);

--
-- Indices de la tabla `purchaseorder`
--
ALTER TABLE `purchaseorder`
  ADD PRIMARY KEY (`OrderId`,`SupplierId`),
  ADD KEY `PurchaseOder_SupplierId` (`SupplierId`);

--
-- Indices de la tabla `purchaseorderitems`
--
ALTER TABLE `purchaseorderitems`
  ADD PRIMARY KEY (`OrderId`,`ProductId`),
  ADD KEY `fk_poitems_product` (`ProductId`);

--
-- Indices de la tabla `suppliers`
--
ALTER TABLE `suppliers`
  ADD PRIMARY KEY (`SupplierId`,`Email`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `products`
--
ALTER TABLE `products`
  MODIFY `ProductId` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT de la tabla `purchaseorder`
--
ALTER TABLE `purchaseorder`
  MODIFY `OrderId` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de la tabla `suppliers`
--
ALTER TABLE `suppliers`
  MODIFY `SupplierId` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Restricciones para tablas volcadas
--

--
/*
-- Filtros para la tabla `inventory`
--
ALTER TABLE `inventory`
  ADD CONSTRAINT `inventory.productId` FOREIGN KEY (`ProductId`) REFERENCES `products` (`ProductId`) ON DELETE RESTRICT ON UPDATE RESTRICT;*/

--
-- Filtros para la tabla `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `SupplierId` FOREIGN KEY (`SupplierId`) REFERENCES `suppliers` (`SupplierId`) ON DELETE RESTRICT ON UPDATE RESTRICT;

--
-- Filtros para la tabla `purchaseorder`
--
ALTER TABLE `purchaseorder`
  ADD CONSTRAINT `PurchaseOder_SupplierId` FOREIGN KEY (`SupplierId`) REFERENCES `suppliers` (`SupplierId`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Filtros para la tabla `purchaseorderitems`
--
ALTER TABLE `purchaseorderitems`
  ADD CONSTRAINT `fk_poitems_order` FOREIGN KEY (`OrderId`) REFERENCES `purchaseorder` (`OrderId`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_poitems_product` FOREIGN KEY (`ProductId`) REFERENCES `products` (`ProductId`) ON DELETE RESTRICT ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
