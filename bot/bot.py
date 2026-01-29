"""
Telegram бот для регистрации пользователей сайта Роскарниз.

Процесс регистрации:
1. Пользователь пишет /start
2. Бот просит поделиться номером телефона
3. Пользователь отправляет контакт
4. Бот просит придумать пароль
5. Пользователь вводит пароль
6. Бот регистрирует пользователя через API
"""

import os
import logging
import requests
from dotenv import load_dotenv
from telegram import Update, KeyboardButton, ReplyKeyboardMarkup, ReplyKeyboardRemove
from telegram.ext import (
    Application,
    CommandHandler,
    MessageHandler,
    ConversationHandler,
    ContextTypes,
    filters,
)

# Загрузка переменных окружения
load_dotenv()

# Настройка логирования
logging.basicConfig(
    format="%(asctime)s - %(name)s - %(levelname)s - %(message)s", level=logging.INFO
)
logger = logging.getLogger(__name__)

# Переменные окружения
TELEGRAM_BOT_TOKEN = os.getenv("TELEGRAM_BOT_TOKEN")
API_URL = os.getenv("API_URL", "http://localhost:8000/api")

# Состояния диалога
WAITING_FOR_PHONE, WAITING_FOR_PASSWORD = range(2)


async def start(update: Update, context: ContextTypes.DEFAULT_TYPE) -> int:
    """Начало регистрации — приветствие и запрос номера телефона."""
    
    # Кнопка для отправки номера телефона
    keyboard = [
        [KeyboardButton("📱 Поделиться номером", request_contact=True)]
    ]
    reply_markup = ReplyKeyboardMarkup(keyboard, one_time_keyboard=True, resize_keyboard=True)
    
    await update.message.reply_text(
        "👋 Добро пожаловать в Роскарниз!\n\n"
        "Для регистрации на сайте нажмите кнопку ниже, "
        "чтобы поделиться своим номером телефона.",
        reply_markup=reply_markup,
    )
    
    return WAITING_FOR_PHONE


async def phone_received(update: Update, context: ContextTypes.DEFAULT_TYPE) -> int:
    """Получение номера телефона и запрос пароля."""
    
    contact = update.message.contact
    
    if contact is None:
        await update.message.reply_text(
            "❌ Пожалуйста, используйте кнопку для отправки номера телефона."
        )
        return WAITING_FOR_PHONE
    
    # Проверяем, что пользователь отправил свой номер
    if contact.user_id != update.effective_user.id:
        await update.message.reply_text(
            "❌ Пожалуйста, отправьте свой собственный номер телефона."
        )
        return WAITING_FOR_PHONE
    
    # Сохраняем данные в контексте
    context.user_data["phone"] = contact.phone_number
    context.user_data["telegram_id"] = update.effective_user.id
    context.user_data["name"] = update.effective_user.full_name
    
    await update.message.reply_text(
        f"✅ Номер получен: {contact.phone_number}\n\n"
        "Теперь придумайте пароль для входа на сайт.\n"
        "Пароль должен содержать минимум 6 символов.",
        reply_markup=ReplyKeyboardRemove(),
    )
    
    return WAITING_FOR_PASSWORD


async def password_received(update: Update, context: ContextTypes.DEFAULT_TYPE) -> int:
    """Получение пароля и регистрация пользователя."""
    
    password = update.message.text
    
    if len(password) < 6:
        await update.message.reply_text(
            "❌ Пароль слишком короткий. Минимум 6 символов.\n"
            "Попробуйте ещё раз:"
        )
        return WAITING_FOR_PASSWORD
    
    # Удаляем сообщение с паролем для безопасности
    try:
        await update.message.delete()
    except Exception:
        pass  # Не критично если не удалось удалить
    
    # Регистрация через API
    phone = context.user_data.get("phone")
    telegram_id = context.user_data.get("telegram_id")
    name = context.user_data.get("name")
    
    try:
        response = requests.post(
            f"{API_URL}/auth/register",
            json={
                "phone": phone,
                "password": password,
                "telegram_id": telegram_id,
                "name": name,
            },
            timeout=10,
        )
        
        if response.status_code == 201:
            await update.message.reply_text(
                "🎉 Регистрация успешна!\n\n"
                f"📱 Ваш логин: {phone}\n"
                "🔐 Пароль: тот, что вы только что ввели\n\n"
                "Теперь вы можете войти на сайт roskarniz.ru"
            )
            
        elif response.status_code == 422:
            # Ошибка валидации (скорее всего пользователь уже есть)
            errors = response.json().get("errors", {})
            
            if "phone" in errors:
                await update.message.reply_text(
                    "❌ Этот номер телефона уже зарегистрирован.\n"
                    "Используйте его для входа на сайт."
                )
            elif "telegram_id" in errors:
                await update.message.reply_text(
                    "❌ Вы уже зарегистрированы с этого Telegram аккаунта."
                )
            else:
                await update.message.reply_text(
                    f"❌ Ошибка регистрации: {response.json()}"
                )
        else:
            await update.message.reply_text(
                f"❌ Ошибка сервера: {response.status_code}"
            )
            
    except requests.RequestException as e:
        logger.error(f"API request failed: {e}")
        await update.message.reply_text(
            "❌ Не удалось связаться с сервером. Попробуйте позже."
        )
    
    # Очищаем данные
    context.user_data.clear()
    
    return ConversationHandler.END


async def cancel(update: Update, context: ContextTypes.DEFAULT_TYPE) -> int:
    """Отмена регистрации."""
    
    context.user_data.clear()
    
    await update.message.reply_text(
        "❌ Регистрация отменена.\n"
        "Напишите /start чтобы начать заново.",
        reply_markup=ReplyKeyboardRemove(),
    )
    
    return ConversationHandler.END


def main() -> None:
    """Запуск бота."""
    
    if not TELEGRAM_BOT_TOKEN:
        logger.error("TELEGRAM_BOT_TOKEN не найден в .env файле!")
        return
    
    # Создаём приложение
    application = Application.builder().token(TELEGRAM_BOT_TOKEN).build()
    
    # Диалог регистрации
    conv_handler = ConversationHandler(
        entry_points=[CommandHandler("start", start)],
        states={
            WAITING_FOR_PHONE: [
                MessageHandler(filters.CONTACT, phone_received),
            ],
            WAITING_FOR_PASSWORD: [
                MessageHandler(filters.TEXT & ~filters.COMMAND, password_received),
            ],
        },
        fallbacks=[CommandHandler("cancel", cancel)],
    )
    
    application.add_handler(conv_handler)
    
    # Запуск (совместимость с Python 3.14)
    import asyncio
    logger.info("Бот запущен...")
    
    async def run_bot():
        await application.initialize()
        await application.start()
        await application.updater.start_polling(allowed_updates=Update.ALL_TYPES)
        
        # Держим бота запущенным
        try:
            while True:
                await asyncio.sleep(1)
        except asyncio.CancelledError:
            pass
        finally:
            await application.updater.stop()
            await application.stop()
            await application.shutdown()
    
    try:
        asyncio.run(run_bot())
    except KeyboardInterrupt:
        logger.info("Бот остановлен")


if __name__ == "__main__":
    main()
