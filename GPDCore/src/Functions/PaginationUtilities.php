<?php

namespace GPDCore\Functions;

function encodeCursor($cursor): string
{
    return base64_encode($cursor);
};
function decodeCursor($encodedCursor): string
{
    return base64_decode($encodedCursor);
};
