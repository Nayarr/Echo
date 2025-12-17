#!/usr/bin/env python3
# -*- coding: utf-8 -*-

import mysql.connector
from mysql.connector import Error

try:
    # Connexion à MySQL
    conn = mysql.connector.connect(
        host='localhost',
        user='root',
        password='',
        database='Echo'
    )
    
    cursor = conn.cursor()
    
    # Script SQL de création
    sql = """
    CREATE TABLE IF NOT EXISTS utilisateur (
        id_utilisateur INT AUTO_INCREMENT PRIMARY KEY,
        Prenom VARCHAR(100) NOT NULL,
        nom VARCHAR(100) NOT NULL,
        email VARCHAR(255) NOT NULL UNIQUE,
        mdp VARCHAR(255) NOT NULL,
        ProfilUtilisateur ENUM('Chercheur', 'Etudiant', 'GrandPublic') NOT NULL DEFAULT 'GrandPublic',
        date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        date_modification TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_email (email)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    """
    
    cursor.execute(sql)
    conn.commit()
    
    print("✓ Table utilisateur créée avec succès !")
    
except Error as e:
    print(f"✗ Erreur : {e}")

finally:
    if conn.is_connected():
        cursor.close()
        conn.close()
