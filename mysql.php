<?php

    $connection=mysqli_connect("localhost","root","asdfjkl;", "fakeFoodbank2");

    if (mysqli_connect_errno($connection)){
	    echo "Failed to connect to MySQL: " . mysqli_connect_error();
    }

    mysqli_query($connection, "drop table Client");
    mysqli_query($connection, "drop table Logs");

    mysqli_query($connection, "drop table Agency");	
    mysqli_query($connection, "drop table NatureOfNeed"); 
    
    mysqli_query($connection, "drop table Voucher"); 
    mysqli_query($connection, "drop table Exchange");

    mysqli_query($connection, "drop table FoodParcel");
    mysqli_query($connection, "drop table FoodItem");
    mysqli_query($connection, "drop table FoodParcelType");	
    mysqli_query($connection, "drop table FPType_Contains"); 

    mysqli_query($connection, "drop table Warehouse");	
    mysqli_query($connection, "drop table Store");
	    
    mysqli_query($connection, "drop table DistributionPoint");

    mysqli_query($connection, "drop table Users");
    mysqli_query($connection, "drop table Volunteers");
    mysqli_query($connection, "drop table Donation");

    
    mysqli_query($connection, "drop table ReportedProblems");
	    
    mysqli_query($connection, "drop table Work_warehouse");
    mysqli_query($connection, "drop table Work_agency");
    mysqli_query($connection, "drop table Work_dp");


    mysqli_query($connection, "create table Client(id int not null auto_increment,
	title varchar(5),
	forename varchar(15),
	familyName varchar(15),
	dateOfBirth date,
	gender varchar(7),
	address1 varchar(32),
	address2 varchar(32),
	postcode varchar(10),
	town varchar(32),
	ethnicBackground varchar(15),
	primary key (id),
	oldAddress longtext)");
												    
    mysqli_query($connection, "create table Agency(id int not null auto_increment, 
	organisation varchar(32),
	referralCentreReference varchar(15),
	homeTelephone varchar(15),
	mobileTelephone varchar(15),
	address1 varchar(32),
	address2 varchar(32),
	postcode varchar(10),
	town varchar(32),
	primary key (id))");

    mysqli_query($connection, "create table Voucher(id int not null auto_increment, 
	numberOfAdults int,
	numberOfChildren int,
	wasExchanged boolean,
	agencyVoucherReference varchar(20),
	helping longtext,
	dateVoucherIssued date,
	idAgency int,
	idClient int,
	foreign key (idAgency) references Agency(id),
	foreign key (idClient) references Client (id),
	primary key (id))");							
    
    mysqli_query($connection, "create table NatureOfNeed(nature varchar(32),
	idVoucher int,
	foreign key (idVoucher) references Voucher (id))");

    mysqli_query($connection, "create table FoodParcel(id int not null auto_increment, 
	expiryDate date,
	referenceNumber varchar(10),
	packingDate date,
	wasGiven boolean,
	idAgency int,
	idDP int, 
	idWarehouse int,
	idFPType int,
	idVoucher int,
	foreign key (id) references Voucher(id),
	primary key (id))");	

    mysqli_query($connection, "create table Exchange(id int not null auto_increment, 
	pointOfIssue int,
	pointOfIssueType varchar(10),
	date date,
	idVoucher int,
	foreign key (idVoucher) references Voucher (id),
	primary key (id))");

    mysqli_query($connection, "create table FoodItem(id int not null auto_increment, 
	Name varchar(32),
	primary key (id))");
												    
    mysqli_query($connection, "create table FoodParcelType(id int not null auto_increment, 
	tagColour varchar(15),
	startingLetter varchar(2),
	name varchar(15),
	primary key (id))");

    mysqli_query($connection, "create table FPType_Contains(quantity int,
	idFoodParcelType int,
	idFoodItem int,
	foreign key (idFoodItem) references FoodItem(id),
	foreign key (idFoodParcelType) references FoodParcelType(id))"); 

    mysqli_query($connection, "create table Warehouse(id int not null auto_increment, 
	centralWarehouseName varchar(32),
	address1 varchar(32),
	address2 varchar(32),
	postcode varchar(10),
	town varchar(32),
	homeTelephone varchar(15),
	mobileTelephone varchar(15),
	primary key (id))");

    mysqli_query($connection, "create table Donation(id int not null auto_increment, 
	name varchar(32),
	date date,
	idWarehouse int,
	total int,
	foreign key (idWarehouse) references Warehouse(id),
	primary key (id),
	items longtext)");															

    mysqli_query($connection, "create table Store(quantity int,
	idFoodItem int,
	idWarehouse int,
	foreign key (idFoodItem) references FoodItem(id),
	foreign key (idWarehouse) references Warehouse(id))");//food item and warehouse

    mysqli_query($connection, "create table DistributionPoint(id int not null auto_increment, 
	distributionPointName varchar(32),
	address1 varchar(32),
	address2 varchar(32),
	postcode varchar(10),
	town varchar(32),
	homeTelephone varchar(15),
	mobileTelephone varchar(15),
	primary key (id))");

    mysqli_query($connection, "create table Users (id int not null auto_increment, 
	login varchar(15),
	password varchar(41),
	auth int,
	title varchar(5),
	forename varchar (15),
	familyName varchar (15),
	email varchar (40),
    	enabled tinyint(1),
	primary key (id))");

    mysqli_query($connection, "create table Volunteers(id int not null auto_increment, 
	login varchar(15),
	password varchar(32),
	auth int,
	title varchar(5),
	forename varchar (15),
	familyName varchar (15),
	email varchar (40),
	homeTelephone varchar(15),
	mobileTelephone varchar(15),
	availability varchar(100),
	roles varchar(100),
	address1 varchar(32),
	address2 varchar(32),
	postcode varchar(10),
	town varchar(32),
	primary key (id))");

    mysqli_query($connection, "create table ReportedProblems(id int not null auto_increment, 
	date datetime,
	problem longtext,
	idUsers int,
	foreign key (idUsers) references Users(id),
	primary key (id))");

    mysqli_query($connection, "create table Work_warehouse(idVolunteers int,
	idWarehouse int,
	foreign key (idVolunteers) references Volunteers(id),
	foreign key (idWarehouse) references Warehouse(id))"); //volunterrs and warehouse			


    mysqli_query($connection, "create table Work_agency(idVolunteers int,
	idAgency int,
	foreign key (idVolunteers) references Volunteers(id),
	foreign key (idAgency) references Agency(id))"); //volunterrs and agency			

    mysqli_query($connection, "create table Work_dp(idVolunteers int,
	idDistributionPoint int,
	foreign key (idVolunteers) references Volunteers(id),
	foreign key (idDistributionPoint) references DistributionPoint(id))"); //volunterrs and distribution point			

    mysqli_query($connection, "create table Logs(id int not null auto_increment, 
	date datetime,
	action longtext,
	idUsers int,
	primary key (id))");

    mysqli_close($connection);
?>
