-- Insert Publishers
INSERT INTO Publisher (name) VALUES
('Fantasy Flight Games'),
('Days of Wonder'),
('CMON'),
('Stonemaier Games'),
('Z-Man Games'),
('Asmodee');

-- Insert Designers
INSERT INTO Designer (first_name, last_name) VALUES
('Klaus', 'Teuber'),
('Matt', 'Leacock'),
('Jamey', 'Stegmaier'),
('Eric', 'Lang'),
('Bruno', 'Cathala'),
('Antoine', 'Bauza');

-- Insert Artists
INSERT INTO Artist (first_name, last_name) VALUES
('Miguel', 'Coimbra'),
('Jakub', 'Rozalski'),
('Andrew', 'Bosley'),
('David', 'Griffith'),
('Cyrille', 'Daujean'),
('Pascal', 'Quidault');

-- Insert Board Games
INSERT INTO BoardGame (title, tagline, year, description, player_range, age_range, playtime_range, image, tags) VALUES
('Scythe', 'Alternate history meets worker placement', 2016, 'In an alternate-history 1920s period, players attempt to earn their fortune and claim their faction''s stake in the land around the mysterious Factory.', '1-5', '14+', '90-120', 'board_1.png', 'strategy,area control,resource management'),

('Pandemic Legacy', 'Save humanity, one month at a time', 2015, 'A cooperative legacy game of global disease fighting that evolves as you play through the campaign.', '2-4', '13+', '60', 'board_2.png', 'cooperative,legacy,strategy'),

('Blood Rage', 'Viking glory in Ragnarök', 2015, 'Lead your Viking clan to glory during Ragnarök, drafting cards and battling in this mythic game.', '2-4', '14+', '60-90', 'board_3.png', 'area control,miniatures,card drafting'),

('Wingspan', 'A competitive bird-collection engine builder', 2019, 'Attract a beautiful and diverse collection of birds to your wildlife preserve.', '1-5', '10+', '40-70', 'board_4.png', 'card game,engine building,nature'),

('Ticket to Ride', 'Cross-country train adventure', 2004, 'Build your railroad across North America, connecting cities and completing tickets.', '2-5', '8+', '30-60', 'board_5.png', 'family,route building,set collection'),

('7 Wonders', 'Build your ancient civilization', 2010, 'Draft cards to develop your ancient civilization and build your Wonder of the World.', '2-7', '10+', '30', 'board_6.png', 'card drafting,civilization,strategy'),

('Carcassonne', 'Medieval tile-laying tactics', 2000, 'Build the landscape of a medieval fortress city one tile at a time.', '2-5', '7+', '30-45', 'board_7.png', 'tile placement,medieval,family'),

('Azul', 'Tile placement artistry', 2017, 'Compete to build the most beautiful mosaic on the walls of the Royal Palace.', '2-4', '8+', '30-45', 'board_8.png', 'abstract,tile placement,strategy'),

('Terraforming Mars', 'Race to make Mars habitable', 2016, 'Compete with rival CEOs to transform Mars into a habitable planet.', '1-5', '12+', '120', 'board_9.png', 'science fiction,engine building,strategy'),

('Gloomhaven', 'Epic tactical dungeon crawler', 2017, 'A persistent world of tactical combat and branching narratives.', '1-4', '14+', '90-150', 'board_10.png', 'dungeon crawler,campaign,tactical combat'),

('Spirit Island', 'Defend your island from colonizers', 2017, 'Work together as nature spirits to defend your island home from colonizing invaders.', '1-4', '13+', '90-120', 'board_11.png', 'cooperative,strategy,fantasy'),

('Everdell', 'Woodland creature civilization', 2018, 'Build a city of cards in this woodland worker placement game.', '1-4', '13+', '40-80', 'board_12.png', 'worker placement,card game,fantasy');

-- Insert BoardGame-Publisher relationships
INSERT INTO BoardGame_Publisher (boardgame_id, publisher_id) VALUES
(1, 4), -- Scythe - Stonemaier
(2, 5), -- Pandemic Legacy - Z-Man
(3, 3), -- Blood Rage - CMON
(4, 4), -- Wingspan - Stonemaier
(5, 2), -- Ticket to Ride - Days of Wonder
(6, 6), -- 7 Wonders - Asmodee
(7, 5), -- Carcassonne - Z-Man
(8, 6), -- Azul - Asmodee
(9, 4), -- Terraforming Mars - Stonemaier
(10, 3), -- Gloomhaven - CMON
(11, 1), -- Spirit Island - Fantasy Flight
(12, 1); -- Everdell - Fantasy Flight

-- Insert BoardGame-Designer relationships
INSERT INTO BoardGame_Designer (boardgame_id, designer_id) VALUES
(1, 3), -- Scythe - Jamey Stegmaier
(2, 2), -- Pandemic Legacy - Matt Leacock
(3, 4), -- Blood Rage - Eric Lang
(4, 3), -- Wingspan - Jamey Stegmaier
(5, 5), -- Ticket to Ride - Bruno Cathala
(6, 6), -- 7 Wonders - Antoine Bauza
(7, 1), -- Carcassonne - Klaus Teuber
(8, 5), -- Azul - Bruno Cathala
(9, 3), -- Terraforming Mars - Jamey Stegmaier
(10, 4), -- Gloomhaven - Eric Lang
(11, 2), -- Spirit Island - Matt Leacock
(12, 6); -- Everdell - Antoine Bauza

-- Insert BoardGame-Artist relationships
INSERT INTO BoardGame_Artist (boardgame_id, artist_id) VALUES
(1, 2), -- Scythe - Jakub Rozalski
(2, 4), -- Pandemic Legacy - David Griffith
(3, 1), -- Blood Rage - Miguel Coimbra
(4, 3), -- Wingspan - Andrew Bosley
(5, 5), -- Ticket to Ride - Cyrille Daujean
(6, 6), -- 7 Wonders - Pascal Quidault
(7, 1), -- Carcassonne - Miguel Coimbra
(8, 3), -- Azul - Andrew Bosley
(9, 4), -- Terraforming Mars - David Griffith
(10, 2), -- Gloomhaven - Jakub Rozalski
(11, 6), -- Spirit Island - Pascal Quidault
(12, 5); -- Everdell - Cyrille Daujean 