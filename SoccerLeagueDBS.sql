drop table Employs;
drop table Plays;
drop table Receives;
drop table PlaysIn;

drop table Sponsor;
drop table Staff;

drop table InjuryReport;

drop table PlayerMember;
drop table PlayerInfo;

drop table Team;
drop table Ranking;
drop table HomeStadium;

drop table Game;
drop table Referee;
drop table MedicalStaff;

ALTER SESSION SET NLS_DATE_FORMAT = 'YYYY-MM-DD';

create table Game
    (gameID int primary key,
    gameDate Date,
    homeScore int,
    awayScore int);

grant select on Game to public;

create table Referee
    (refereeID int primary key,
    refName varchar(30) not null);

grant select on Referee to public;

create table MedicalStaff
    (mstaffID int primary key,
    mstaffName varchar(20) not null,
    mstaffRole varchar(20));

grant select on MedicalStaff to public;

create table Ranking
    (ranking int primary key,
    teamName varchar(30) not null);

grant select on Ranking to public;

create table HomeStadium
    (stadiumID int primary key,
    stadiumName varchar(30) not null,
    capacity int);

grant select on HomeStadium to public;

create table Team
    (teamID int primary key,
    stadiumID int not null unique,
    ranking int not null,
    city varchar(30) not null,
    foreign key (ranking) references Ranking ON DELETE SET NULL,
    foreign key (stadiumID) references HomeStadium ON DELETE CASCADE);

grant select on Team to public;

-- changed primary key (playerName, pnumber,teamID) to primary key (playerName, pnumber,teamID)
-- "foreign key (stadiumID) references HomeStadium ON DELETE CASCADE);" from the Team table tries to set teamID to NULL but cannot
-- because teamID is part of the primary key of PlayerInfo
create table PlayerInfo
    (playerName varchar(30) not null,
    pnumber int,
    teamID int,
    playerSalary int,
    position varchar(20),
    goalNum int,
    primary key (playerName, pnumber),
    foreign key (teamID) references Team ON DELETE SET NULL);

grant select on PlayerInfo to public;

create table PlayerMember
    (memberID int primary key,
    playerName varchar(30) not null,
    pnumber int,
    teamID int,
    foreign key (playerName, pnumber) references PlayerInfo ON DELETE CASCADE);

grant select on PlayerMember to public;

create table InjuryReport
    (injID int,
    memberID int,
    injType varchar(50),
    injDate Date,
    primary key (injID, memberID),
    foreign key (memberID) references PlayerMember ON DELETE CASCADE);

grant select on InjuryReport to public;

create table Staff
    (memberID int primary key,
    staffName varchar(30) not null,
    staffSalary int,
    staffrole varchar(20),
    teamID int,
    foreign key (teamID) references Team ON DELETE SET NULL);

grant select on Staff to public;

-- Seemed weird to have a fee of null - sunny
-- Easer to have 
create table Sponsor
    (sponsorID int primary key,
    sponsorName varchar(30),
    fee int default 0,
    teamID int,
    foreign key (teamID) references Team ON DELETE SET NULL);

grant select on Sponsor to public;

create table PlaysIn
    (gameID int,
    stadiumID int,
    primary key (gameID, stadiumID),
    foreign key (gameID) references Game ON DELETE CASCADE,
    foreign key (stadiumID) references HomeStadium ON DELETE CASCADE);

grant select on PlaysIn to public;

create table Receives
    (mstaffID int,
    memberID int,
    injID int,
    primary key (mstaffID, memberID, injID),
    foreign key (mstaffID) references MedicalStaff ON DELETE SET NULL,
    foreign key (injID, memberID) references InjuryReport ON DELETE CASCADE);

grant select on Receives to public;

create table Plays
    (gameID int,
    teamID1 int,
    teamID2 int,
    primary key (gameID, teamID1, teamID2),
    foreign key (gameID) references Game ON DELETE CASCADE,
    foreign key (teamID1) references Team on DELETE CASCADE,
    foreign key (teamID2) references Team on DELETE CASCADE);

grant select on Plays to public;

create table Employs
    (gameID int,
    refereeID int,
    primary key(gameID, refereeID),
    foreign key (gameID) references Game ON DELETE CASCADE,
    foreign key (refereeID) references Referee ON DELETE CASCADE);

grant select on Employs to public;


insert into Game 
values(1, '2022-01-01', 2, 1);

insert into Game 
values(2, '2022-01-05', 0, 0);

insert into Game
values(3, '2022-01-12', 3, 2);

insert into Game
values(4, '2022-01-20', 1, 1);

insert into Game
values(5, '2022-01-27', 2, 3);


insert into Referee 
values(1, 'John Smith');

insert into Referee
values(2, 'Mary Johnson');

insert into Referee
values(3, 'David Lee');

insert into Referee
values(4, 'Emily Wong');

insert into Referee
values(5, 'Michael Chen');


insert into MedicalStaff
values(1, 'Dr. James Lee', 'Physician');

insert into MedicalStaff
values(2, 'Dr. Susan Kim', 'Physiotherapist');

insert into MedicalStaff
values(3, 'Dr. David Park', 'Chiropractor');

insert into MedicalStaff
values(4, 'Dr. Emily Chen', 'Massage Therapist');

insert into MedicalStaff
values(5, 'Dr. Michael Wang', 'Athletic Trainer');


insert into Ranking 
values(1, 'Manchester City');

insert into Ranking
values(2, 'Manchester United');

insert into Ranking
values(3, 'Chelsea');

insert into Ranking
values(4, 'Liverpool');

insert into Ranking
values(8, 'Arsenal');

insert into Ranking
values(9, 'Bayearn');

insert into HomeStadium
values(1, 'Old Trafford', 74879);

insert into HomeStadium
values(2, 'Etihad Stadium', 55017);

insert into HomeStadium
values(3, 'Anfield', 53394);

insert into HomeStadium
values(4, 'Emirates Stadium', 60260);

insert into HomeStadium
values(5, 'Stamford Bridge', 40853);

insert into HomeStadium
values(6, 'Michigan Stadium', 30291);


insert into Team
values(1, 1, 2, 'Manchester');

insert into Team
values(2, 2, 1, 'Manchester');

insert into Team
values(3, 3, 4, 'Liverpool');

insert into Team
values(4, 4, 8, 'London');

insert into Team
values(5, 5, 3, 'London');



insert into PlayerInfo
values('David Beckham', 7, 1, 1000000, 'Forward', 10);

insert into PlayerInfo
values('Cristiano Ronaldo', 10, 1, 2000000, 'Forward', 25);

insert into PlayerInfo
values('Lionel Messi', 10, 2, 2500000, 'Forward', 30);

insert into PlayerInfo
values('Neymar Jr', 10, 3, 1800000, 'Forward', 20);

insert into PlayerInfo
values('Kylian Mbappe', 7, 4, 1500000, 'Forward', 15);


insert into PlayerMember
values(1, 'David Beckham', 7, 1);

insert into PlayerMember
values(2, 'Cristiano Ronaldo', 10, 1);

insert into PlayerMember
values(3, 'Lionel Messi', 10, 2);

insert into PlayerMember
values(4, 'Neymar Jr', 10, 3);

insert into PlayerMember
values(5, 'Kylian Mbappe', 7, 4);


insert into InjuryReport
values(1, 1, 'Ankle Sprain', '2022-02-10');

insert into InjuryReport
values(2, 3, 'Hamstring Strain', '2022-02-15');

insert into InjuryReport
values(3, 5, 'Knee Injury', '2022-02-17');

insert into InjuryReport
values(4, 2, 'Concussion', '2022-02-20');

insert into InjuryReport
values(5, 4, 'Groin Strain', '2022-02-25');

insert into InjuryReport
values(6, 1, 'Knee Injury', '2023-03-27');


insert into Staff
values(6, 'Tom Lee', 50000, 'Manager', 1);

insert into Staff
values(7, 'Grace Kim', 40000, 'Coach', 1);

insert into Staff
values(8, 'Emma Liu', 35000, 'Trainer', 2);

insert into Staff
values(9, 'Kevin Chen', 60000, 'Manager', 3);

insert into Staff
values(10, 'Sophia Wang', 45000, 'Coach', 4);


insert into Sponsor
values(1,'Coca-Cola', 1000000, 1);

insert into Sponsor
values(2,'Nike', 1500000, 2);

insert into Sponsor
values(3,'Pepsi', 800000, 3);

insert into Sponsor
values(4,'Adidas', 1200000, 4);

insert into Sponsor
values(5,'Samsung', 900000, 5);


insert into PlaysIn
values(1, 1);

insert into PlaysIn
values(2, 2);

insert into PlaysIn
values(3, 3);

insert into PlaysIn
values(4, 4);

insert into PlaysIn
values(5, 5);


insert into Receives
values(1, 1, 1);

insert into Receives
values(2, 3, 2);

insert into Receives
values(3, 5, 3);

insert into Receives
values(4, 2, 4);

insert into Receives
values(5, 4, 5);


insert into Plays
values(1, 1, 2);

insert into Plays
values(2, 3, 4);

insert into Plays
values(3, 5, 1);

insert into Plays
values(4, 2, 3);

insert into Plays
values(5, 4, 5);


insert into Employs
values(1, 1);

insert into Employs
values(1, 3);

insert into Employs
values(2, 2);

insert into Employs
values(2, 3);

insert into Employs
values(3, 3);

insert into Employs
values(4, 3);

insert into Employs
values(4, 4);

insert into Employs
values(5, 3);

insert into Employs
values(5, 4);

insert into Employs
values(5, 5);
