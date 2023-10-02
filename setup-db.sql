DROP DATABASE db

CREATE DATABASE `db`

USE db

CREATE TABLE restaurants (
    id int UNSIGNED NOT NULL AUTO_INCREMENT,
    name varchar(255) NOT NULL,
    description varchar(255) NOT NULL,
    image varchar(255) NOT NULL,
    status_id int UNSIGNED NOT NULL DEFAULT(1),
    primary_color varchar(255) NOT NULL DEFAULT('#EC0032'),
    secondary_color varchar(255) NOT NULL DEFAULT('#F7F7F7'),
    tertiaty_color varchar(255) NOT NULL DEFAULT('#FFFFFF'),
    created_at DATETIME NOT NULL DEFAULT(current_timestamp),
    updated_at DATETIME NOT NULL DEFAULT(current_timestamp),
    PRIMARY KEY (id)
);

CREATE TABLE officials (
    id int UNSIGNED NOT NULL AUTO_INCREMENT,
    name varchar(255) NOT NULL,
    email varchar(255),
    password varchar(255),
    status_id int UNSIGNED NOT NULL DEFAULT(1),
    restaurant_id int UNSIGNED,
    image_id int UNSIGNED NOT NULL,
    created_at DATETIME NOT NULL DEFAULT(current_timestamp),
    updated_at DATETIME NOT NULL DEFAULT(current_timestamp),
    PRIMARY KEY (id),
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id)
);

CREATE TABLE users (
    id int UNSIGNED NOT NULL AUTO_INCREMENT,
    name varchar(255) NOT NULL,
    email varchar(255),
    password varchar(255),
    status_id int UNSIGNED NOT NULL DEFAULT(1),
    image_id int UNSIGNED NOT NULL,
    created_at DATETIME NOT NULL DEFAULT(current_timestamp),
    updated_at DATETIME NOT NULL DEFAULT(current_timestamp),
    PRIMARY KEY (id)
);

CREATE TABLE products (
    id int UNSIGNED NOT NULL AUTO_INCREMENT,
    name varchar(255) NOT NULL,
    description varchar(255) NOT NULL,
    price int NOT NULL,
    image varchar(255) NOT NULL,
    status_id int UNSIGNED NOT NULL DEFAULT(1),
    restaurant_id int UNSIGNED NOT NULL,
    created_at DATETIME NOT NULL DEFAULT(current_timestamp),
    updated_at DATETIME NOT NULL DEFAULT(current_timestamp),
    PRIMARY KEY (id),
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id)
);

CREATE TABLE tables (
    id int UNSIGNED NOT NULL AUTO_INCREMENT,
    status_id int UNSIGNED NOT NULL DEFAULT(1),
    restaurant_id int UNSIGNED NOT NULL,
    enter_code varchar(255) NOT NULL,
    number int UNSIGNED NOT NULL,
    created_at DATETIME NOT NULL DEFAULT(current_timestamp),
    updated_at DATETIME NOT NULL DEFAULT(current_timestamp),
    PRIMARY KEY (id),
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id)
);

CREATE TABLE sessions (
    id int UNSIGNED NOT NULL AUTO_INCREMENT,
    status_id int UNSIGNED NOT NULL DEFAULT(1),
    table_id int UNSIGNED NOT NULL,
    created_at DATETIME NOT NULL DEFAULT(current_timestamp),
    updated_at DATETIME NOT NULL DEFAULT(current_timestamp),
    PRIMARY KEY (id),
    FOREIGN KEY (table_id) REFERENCES tables(id)
);

CREATE TABLE session_users (
    id int UNSIGNED NOT NULL AUTO_INCREMENT,
    status_id int UNSIGNED NOT NULL DEFAULT(1),
    session_id int UNSIGNED NOT NULL,
    user_id int UNSIGNED NOT NULL,
    amount_to_pay int NOT NULL DEFAULT(0),
    created_at DATETIME NOT NULL DEFAULT(current_timestamp),
    updated_at DATETIME NOT NULL DEFAULT(current_timestamp),
    PRIMARY KEY (id),
    FOREIGN KEY (session_id) REFERENCES sessions(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE session_waiter_calls (
    id int UNSIGNED NOT NULL AUTO_INCREMENT,
    status_id int UNSIGNED NOT NULL DEFAULT(1),
    session_id int UNSIGNED NOT NULL,
    session_user_id int UNSIGNED NOT NULL,
    created_at DATETIME NOT NULL DEFAULT(current_timestamp),
    updated_at DATETIME NOT NULL DEFAULT(current_timestamp),
    PRIMARY KEY (id),
    FOREIGN KEY (session_id) REFERENCES sessions(id),
    FOREIGN KEY (session_user_id) REFERENCES session_users(id)
);

CREATE TABLE session_orders (
    id int UNSIGNED NOT NULL AUTO_INCREMENT,
    status_id int UNSIGNED NOT NULL DEFAULT(1),
    session_id int UNSIGNED NOT NULL,
    product_id int UNSIGNED NOT NULL,
    quantity int UNSIGNED NOT NULL,
    amount int NOT NULL,
    amount_left int NOT NULL,
    created_at DATETIME NOT NULL DEFAULT(current_timestamp),
    updated_at DATETIME NOT NULL DEFAULT(current_timestamp),
    PRIMARY KEY (id),
    FOREIGN KEY (session_id) REFERENCES sessions(id),
    FOREIGN KEY (product_id) REFERENCES products(id)
);

CREATE TABLE session_order_users (
    id int UNSIGNED NOT NULL AUTO_INCREMENT,
    status_id int UNSIGNED NOT NULL DEFAULT(1),
    session_order_id int UNSIGNED NOT NULL,
    session_user_id int UNSIGNED NOT NULL,
    created_at DATETIME NOT NULL DEFAULT(current_timestamp),
    updated_at DATETIME NOT NULL DEFAULT(current_timestamp),
    PRIMARY KEY (id),
    FOREIGN KEY (session_order_id) REFERENCES session_orders(id),
    FOREIGN KEY (session_user_id) REFERENCES session_users(id)
);

CREATE TABLE error_logs (
    id int UNSIGNED NOT NULL AUTO_INCREMENT,
    file varchar(255) NOT NULL,
    line int UNSIGNED NOT NULL,
    message varchar(255) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT(current_timestamp),
    updated_at DATETIME NOT NULL DEFAULT(current_timestamp),
    PRIMARY KEY (id)
);

INSERT INTO restaurants 
(name, description, image) 
VALUES 
('Torino Trattoria', 
'Desfrute de pratos deliciosos e refinados, que transmitem muito sabor. O ambiente é acolhedor e elegante, perfeito para um almoço de negócios ou para quem busca um momento especial ✨️', 
'https://firebasestorage.googleapis.com/v0/b/quick-order-16.appspot.com/o/Screenshot%202023-06-11%20at%2012.02.50.png?alt=media&token=a95631d0-5849-4193-8d3a-3287b60028d2'),
('Picanha Mania', 
'😋Picanha Mania: Ninguém resiste!️', 
'https://firebasestorage.googleapis.com/v0/b/quick-order-16.appspot.com/o/Screenshot%202023-06-13%20at%2019.32.31.png?alt=media&token=c3711111-18aa-4d2d-b6a1-e41051d8642a');

INSERT INTO products  
(name, description, price, image, restaurant_id) 
VALUES 
(
'Guarnição Mania 4',
'Arroz branco com ovos e batata palha, paçoca de carne seca e purê de batatas.',
6200,
'https://firebasestorage.googleapis.com/v0/b/quick-order-16.appspot.com/o/guarnicao_mania_4.png?alt=media&token=d6c093bb-3403-437f-bad3-6fd6a24595b6',
2
),
(
'Batata Suprema',
'Batata frita coberta com queijo cheddar cremoso e pedaços de bacon fritos.',
3200,
'https://firebasestorage.googleapis.com/v0/b/quick-order-16.appspot.com/o/batata_suprema.png?alt=media&token=87fc8986-be63-47bd-852f-6758fc707542',
2
),
(
'Carne de Sol',
'Porção de 400 gramas',
7900,
'https://firebasestorage.googleapis.com/v0/b/quick-order-16.appspot.com/o/carne_de_sol.png?alt=media&token=1257bbd0-be19-44a1-bfde-ba9e9ff78177',
2
),
(
'Picanha na Brasa',
'Suculenta picanha premium importada, assada na brasa de forma excepcional e em cortes de steak. Não retiramos gordura da peça. Peso in natura de aproximadamente 500g.',
9900,
'https://firebasestorage.googleapis.com/v0/b/quick-order-16.appspot.com/o/picanha_na_brasa.png?alt=media&token=ee9ee813-5e99-48b8-8542-e9e26e389658',
2
),
(
'Arroz Mania Especial',
'Arroz branco com ovos, bacon e batata palha.',
3400,
'https://firebasestorage.googleapis.com/v0/b/quick-order-16.appspot.com/o/arroz_mania_especial.png?alt=media&token=00764bd0-ec5c-40ef-b00a-ee1dd0d5fc7b',
2
),
(
'Salada Tropical',
'Alface americana, alface crespa, alface roxa, manga, abacaxi, molho de mostarda com mel.',
3500,
'https://firebasestorage.googleapis.com/v0/b/quick-order-16.appspot.com/o/salada_tropical.png?alt=media&token=7af03291-07d0-4465-a9bc-7456bf5d54f2',
2
),
(
'Queijo Coalho Empanado',
'Palitos de queijo coalho empanados, servidos com geleia de cupuaçu levemente apimentado.',
3300,
'https://firebasestorage.googleapis.com/v0/b/quick-order-16.appspot.com/o/queijo_coalho_empanado.png?alt=media&token=08753fa7-f742-4f00-8cc2-cd489ea3caa5',
2
);

INSERT INTO tables
(restaurant_id, enter_code, number)
VALUES
(1, 'uazt7ff5mx', 1),
(1, 'o3vsjyv4y7', 2),
(1, '00k9mreg07', 3),
(1, '92mvp13soi', 4),
(2, 'CODE1', 1),
(2, 'z5dldt1fnn', 2),
(2, '2tix8c14wl', 3),
(2, 'y71sy0x5t0', 4);


INSERT INTO officials 
(name, email, password, restaurant_id, image_id)
VALUES
('Nelson', 'nelson@icomp.com', '123456', 2, 1),
('Keren', 'keren@icomp.com', '123456', 1, 2);


SHOW CREATE DATABASE db
