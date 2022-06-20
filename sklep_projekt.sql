-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Czas generowania: 13 Cze 2022, 10:05
-- Wersja serwera: 10.4.20-MariaDB
-- Wersja PHP: 7.3.29

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Baza danych: `sklep_projekt`
--

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `kategorie`
--

CREATE TABLE `kategorie` (
  `id` int(11) NOT NULL,
  `nazwa` varchar(30) COLLATE utf8mb4_polish_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_polish_ci;

--
-- Zrzut danych tabeli `kategorie`
--

INSERT INTO `kategorie` (`id`, `nazwa`) VALUES
(1, 'Inne'),
(2, 'Bułki'),
(3, 'Słone przekąski');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `produkty`
--

CREATE TABLE `produkty` (
  `id` int(11) NOT NULL,
  `nazwa` varchar(30) COLLATE utf8mb4_polish_ci NOT NULL,
  `ilosc` int(11) DEFAULT NULL,
  `data_waznosci` date NOT NULL,
  `kategoria_id` int(11) NOT NULL DEFAULT 1,
  `cena` decimal(5,2) NOT NULL,
  `marza` int(3) NOT NULL,
  `stan` enum('w_sprzedaży','usunięty') COLLATE utf8mb4_polish_ci NOT NULL DEFAULT 'w_sprzedaży'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_polish_ci;

--
-- Zrzut danych tabeli `produkty`
--

INSERT INTO `produkty` (`id`, `nazwa`, `ilosc`, `data_waznosci`, `kategoria_id`, `cena`, `marza`, `stan`) VALUES
(1, 'Precel', 5, '2022-06-15', 3, '1.43', 10, 'w_sprzedaży'),
(2, 'Bułka kajzerka', 10, '2022-06-14', 2, '0.25', 20, 'w_sprzedaży');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `uzytkownicy`
--

CREATE TABLE `uzytkownicy` (
  `id` int(11) NOT NULL,
  `imie` varchar(20) COLLATE utf8mb4_polish_ci NOT NULL,
  `nazwisko` varchar(30) COLLATE utf8mb4_polish_ci NOT NULL,
  `email` varchar(35) COLLATE utf8mb4_polish_ci NOT NULL,
  `haslo` varchar(255) COLLATE utf8mb4_polish_ci NOT NULL,
  `rola` enum('user','admin','owner') COLLATE utf8mb4_polish_ci NOT NULL DEFAULT 'user',
  `newsletter` enum('tak') COLLATE utf8mb4_polish_ci DEFAULT NULL,
  `autoryzacja` enum('tak','nie') COLLATE utf8mb4_polish_ci NOT NULL DEFAULT 'nie',
  `prosba` date DEFAULT NULL,
  `data_ost_log` date DEFAULT NULL,
  `kod` varchar(10) COLLATE utf8mb4_polish_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_polish_ci;

--
-- Zrzut danych tabeli `uzytkownicy`
--

INSERT INTO `uzytkownicy` (`id`, `imie`, `nazwisko`, `email`, `haslo`, `rola`, `newsletter`, `autoryzacja`, `prosba`, `data_ost_log`, `kod`) VALUES
(1, 'Jan', 'Kowalski', 'jko1.PAR@gmail.com', '$2y$10$24omLUWsjjqlSkw4hLFUYebyh0jcG8LbX/PPYggGRLfMwV7fviRAK', 'admin', NULL, 'tak', NULL, '2022-06-06', NULL),
(3, 'Jakub', 'Irla', 'irlajakub@gmail.com', '$2y$10$Cd60tVws87buOCgatxZd0OcX7MupgnYpLbuQe6Q0CmrIsO1HVXULC', 'owner', NULL, 'tak', NULL, '2022-06-13', NULL),
(5, 'Tomasz', 'Bułecki', 'tbulecki@gmail.com', '$2y$10$cGxCzqXdO45N/oYeRXb.m.y557mP9OoqI4kIro3OStwhDCqQye/nW', 'user', 'tak', 'tak', NULL, '2022-06-13', NULL),
(6, 'Charles', 'Baguette', 'charlesbagu@gmail.com', '$2y$10$cGxCzqXdO45N/oYeRXb.m.y557mP9OoqI4kIro3OStwhDCqQye/nW', 'user', 'tak', 'tak', NULL, '2022-05-26', NULL);

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `zamowienia`
--

CREATE TABLE `zamowienia` (
  `id` int(11) NOT NULL,
  `data` datetime DEFAULT NULL,
  `id_uzytkownika` int(11) NOT NULL,
  `stan` enum('koszyk','do realizacji','oczekuje','dostarczono') COLLATE utf8mb4_polish_ci NOT NULL,
  `data_przedawnienia` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_polish_ci;

--
-- Zrzut danych tabeli `zamowienia`
--

INSERT INTO `zamowienia` (`id`, `data`, `id_uzytkownika`, `stan`, `data_przedawnienia`) VALUES
(5, '2022-04-22 13:26:31', 5, 'dostarczono', NULL),
(6, '2022-04-23 06:11:38', 5, 'dostarczono', NULL),
(8, '2022-05-25 13:46:43', 5, 'dostarczono', NULL),
(9, '2022-05-26 17:42:53', 6, 'dostarczono', NULL),
(10, '2022-06-13 07:57:39', 5, 'oczekuje', NULL);

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `zamowione_produkty`
--

CREATE TABLE `zamowione_produkty` (
  `id` int(11) NOT NULL,
  `id_produktu` int(11) NOT NULL,
  `ilosc` int(3) NOT NULL,
  `id_zamowienia` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_polish_ci;

--
-- Zrzut danych tabeli `zamowione_produkty`
--

INSERT INTO `zamowione_produkty` (`id`, `id_produktu`, `ilosc`, `id_zamowienia`) VALUES
(6, 2, 4, 5),
(7, 2, 1, 6),
(9, 1, 2, 5),
(10, 2, 6, 8),
(11, 1, 2, 8),
(12, 2, 10, 9),
(13, 2, 9, 10),
(14, 1, 2, 10);

--
-- Indeksy dla zrzutów tabel
--

--
-- Indeksy dla tabeli `kategorie`
--
ALTER TABLE `kategorie`
  ADD PRIMARY KEY (`id`);

--
-- Indeksy dla tabeli `produkty`
--
ALTER TABLE `produkty`
  ADD PRIMARY KEY (`id`);

--
-- Indeksy dla tabeli `uzytkownicy`
--
ALTER TABLE `uzytkownicy`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indeksy dla tabeli `zamowienia`
--
ALTER TABLE `zamowienia`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_uzytkownika` (`id_uzytkownika`);

--
-- Indeksy dla tabeli `zamowione_produkty`
--
ALTER TABLE `zamowione_produkty`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_zamowienia` (`id_zamowienia`),
  ADD KEY `id_produktu` (`id_produktu`);

--
-- AUTO_INCREMENT dla zrzuconych tabel
--

--
-- AUTO_INCREMENT dla tabeli `kategorie`
--
ALTER TABLE `kategorie`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT dla tabeli `produkty`
--
ALTER TABLE `produkty`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT dla tabeli `uzytkownicy`
--
ALTER TABLE `uzytkownicy`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT dla tabeli `zamowienia`
--
ALTER TABLE `zamowienia`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT dla tabeli `zamowione_produkty`
--
ALTER TABLE `zamowione_produkty`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- Ograniczenia dla zrzutów tabel
--

--
-- Ograniczenia dla tabeli `zamowienia`
--
ALTER TABLE `zamowienia`
  ADD CONSTRAINT `zamowienia_ibfk_2` FOREIGN KEY (`id_uzytkownika`) REFERENCES `uzytkownicy` (`id`);

--
-- Ograniczenia dla tabeli `zamowione_produkty`
--
ALTER TABLE `zamowione_produkty`
  ADD CONSTRAINT `zamowione_produkty_ibfk_1` FOREIGN KEY (`id_zamowienia`) REFERENCES `zamowienia` (`id`),
  ADD CONSTRAINT `zamowione_produkty_ibfk_2` FOREIGN KEY (`id_produktu`) REFERENCES `produkty` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
